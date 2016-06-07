<?php
namespace acceptancesupport\PSB\Core\Scenario;


use acceptancesupport\PSB\Core\Fork\Fork;
use acceptancesupport\PSB\Core\Fork\ProcessManager;
use PSB\Core\Endpoint;

class RunnableScenario
{
    /**
     * @var string
     */
    private $contextFqcn;

    /**
     * @var EndpointBehavior[]
     */
    private $endpointBehaviors = [];

    /**
     * @var PipeSynchronizer[]
     */
    private $pipeSynchronizers = [];

    /**
     * @var EndpointTestExecutionConfiguratorInterface[]
     */
    private $endpointExecutionConfigurators = [];

    /**
     * @var ScenarioContextProxy
     */
    private $contextProxy;

    /**
     * @var ScenarioContext
     */
    private $context;

    /**
     * @var Fork[]
     */
    private $forks = [];

    /**
     * @var ProcessManager
     */
    private $processManager;

    /**
     * @param string                                       $contextFqcn
     * @param EndpointBehavior[]                           $endpointBehaviors
     * @param PipeSynchronizer[]                           $pipeSynchronizers
     * @param EndpointTestExecutionConfiguratorInterface[] $endpointExecutionConfigurators
     */
    public function __construct(
        $contextFqcn,
        array $endpointBehaviors,
        array $pipeSynchronizers,
        array $endpointExecutionConfigurators
    ) {
        $this->contextFqcn = $contextFqcn;
        $this->endpointBehaviors = $endpointBehaviors;
        $this->pipeSynchronizers = $pipeSynchronizers;
        $this->endpointExecutionConfigurators = $endpointExecutionConfigurators;
    }

    /**
     * @param int $timeoutSeconds
     *
     * @return ScenarioRunResult
     */
    public function run($timeoutSeconds)
    {
        $this->createContextProxy();
        $this->initializePipes();
        $callables = $this->describeForks();

        $this->processManager = new ProcessManager();

        foreach ($callables as $endpointFqcn => $callable) {
            if ($this->endpointBehaviors[$endpointFqcn]->isRunInBackground()) {
                $this->forks[$endpointFqcn] = $this->processManager->fork($callable);
            } else {
                $callable();
            }
        }

        $killedForks = $this->waitForks($timeoutSeconds);
        $this->shutdown();

        $errors = [];
        $outputs = [];
        $hangs = [];
        $queueStats = [];
        foreach ($this->forks as $endpointFqcn => $fork) {
            $errors[$endpointFqcn] = $fork->getError();
            $outputs[$endpointFqcn] = $fork->getOutput();
            $hangs[$endpointFqcn] = isset($killedForks[$endpointFqcn]) ? true : false;

            $isSendOnly = $this->endpointBehaviors[$endpointFqcn]->getEndpointConfigProxy()->isSendOnly();
            if (!$isSendOnly) {
                $queueStats[$endpointFqcn] = $this->getQueueStatsFor($endpointFqcn);
            } else {
                $queueStats[$endpointFqcn] = new EndpointQueueStats(0, 0);
            }
        }

        return new ScenarioRunResult($this->context, $errors, $outputs, $hangs, $queueStats);
    }

    public function cleanup()
    {
        if ($this->contextProxy) {
            $this->contextProxy->cleanupStorage();
        }

        foreach ($this->pipeSynchronizers as $pipeSynchronizer) {
            $pipeSynchronizer->cleanup();
        }

        foreach ($this->endpointExecutionConfigurators as $executionConfigurator) {
            $executionConfigurator->cleanup();
        }
    }

    private function createContextProxy()
    {
        $contextFqcn = $this->contextFqcn ?: ScenarioContext::class;

        $this->context = new $contextFqcn();
        $uniqueId = uniqid();
        $storageFilePath = "/tmp/scenario-context-$uniqueId.ser";
        $mutexFilePath = "/tmp/scenario-context-$uniqueId.lock";
        $this->contextProxy = new ScenarioContextProxy(
            $this->context,
            new ScenarioContextStorage($storageFilePath, new Mutex($mutexFilePath))
        );
    }

    private function initializePipes()
    {
        foreach ($this->pipeSynchronizers as $pipeSynchronizer) {
            $pipeSynchronizer->createPipe();
        }
    }

    /**
     * @return \Closure[]
     */
    private function describeForks()
    {
        $callables = [];
        foreach ($this->endpointBehaviors as $endpointConfigProxyFqcn => $endpointBehavior) {
            $endpointConfigProxy = $endpointBehavior->getEndpointConfigProxy();

            $endpointConfigProxy->scenarioContext = $this->contextProxy;

            foreach ($this->endpointExecutionConfigurators as $executionConfigurator) {
                $executionConfigurator->configure($endpointConfigProxy->getConfigurator());
            }

            $endpointConfigProxy->init();

            $callables[$endpointConfigProxyFqcn] = function () use ($endpointConfigProxyFqcn, $endpointBehavior) {
                $endpointConfigurator = $endpointBehavior->getEndpointConfigProxy()->getConfigurator();

                $startableEndpoint = Endpoint::prepare($endpointConfigurator);

                $onPrepared = $endpointBehavior->getOnPrepared();
                if ($onPrepared) {
                    $onPrepared(
                        new RunContext(
                            $endpointConfigProxyFqcn,
                            $this->pipeSynchronizers,
                            $this->contextProxy
                        ),
                        $startableEndpoint->getBusContext()
                    );
                }

                $endpointInstance = $startableEndpoint->start();
                $onStarted = $endpointBehavior->getOnStarted();
                if ($onStarted) {
                    $onStarted(
                        new RunContext(
                            $endpointConfigProxyFqcn,
                            $this->pipeSynchronizers,
                            $this->contextProxy
                        ),
                        $endpointInstance
                    );
                }
            };
        }

        return $callables;
    }

    /**
     * @param int $timeoutSeconds
     *
     * @return array
     */
    private function waitForks($timeoutSeconds)
    {
        $totalForks = count($this->forks);

        if ($totalForks == 0) {
            return [];
        }

        $startTime = microtime(true);

        do {
            usleep(50000);
            $exitedForks = 0;
            foreach ($this->forks as $fork) {
                $fork->wait(false);
                $exitedForks += $fork->isExited() ? 1 : 0;
            }
        } while (microtime(true) - $startTime <= $timeoutSeconds && $exitedForks != $totalForks);

        $killedForks = [];
        foreach ($this->forks as $endpointFqcn => $fork) {
            if (!$fork->isExited()) {
                $killedForks[$endpointFqcn] = $endpointFqcn;
                $fork->kill(SIGTERM);
            }
        }

        if (count($killedForks)) {
            // make sure they stay down
            foreach ($killedForks as $killedFork) {
                posix_kill($this->forks[$killedFork]->getPid(), SIGKILL);
            }
        }

        return $killedForks;
    }

    private function shutdown()
    {
        $this->contextProxy->refreshFromStorage();
        $this->context = $this->contextProxy->getContext();
    }

    /**
     * @param string $endpointFqcn
     *
     * @return EndpointQueueStats
     */
    private function getQueueStatsFor($endpointFqcn)
    {
        $foundConfigurator = null;
        foreach ($this->endpointExecutionConfigurators as $executionConfigurator) {
            if ($executionConfigurator instanceof EndpointQueuesInformationProviderInterface) {
                $foundConfigurator = $executionConfigurator;
                break;
            }
        }

        if ($foundConfigurator) {
            return new EndpointQueueStats(
                $foundConfigurator->getCountOfMessagesInMainQueueOf($endpointFqcn),
                $foundConfigurator->getCountOfMessagesInErrorQueueOf($endpointFqcn)
            );
        }

        return new EndpointQueueStats(0, 0);
    }
}
