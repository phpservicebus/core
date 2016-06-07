<?php
namespace acceptancesupport\PSB\Core\Scenario;


class ScenarioBuilder
{
    /**
     * @var string
     */
    private $contextFqcn;

    /**
     * @var EndpointBehavior[]
     */
    private $endpointBehaviors;

    /**
     * @var RunnableScenario
     */
    private $scenario;
    /**
     * @var
     */
    private $executionConfigurators;

    /**
     * @param EndpointTestExecutionConfiguratorInterface[] $executionConfigurators
     */
    public function __construct(array $executionConfigurators = [])
    {
        $this->executionConfigurators = $executionConfigurators;
    }

    /**
     * @param string $contextFqcn
     *
     * @return ScenarioBuilder
     */
    public function givenContext($contextFqcn)
    {
        $this->contextFqcn = $contextFqcn;
        return $this;
    }

    /**
     * @param EndpointConfiguratorProxy $endpointConfigProxy
     * @param callable                  $onPrepared   It gets called after endpoint is prepared and receives
     *                                                the BusContext as argument
     * @param callable                  $onStarted    It gets called after endpoint is prepared and receives
     *                                                the EndpointInstance as argument
     * @param bool                      $isRunInBackground
     *
     * @return ScenarioBuilder
     */
    public function givenEndpoint(
        EndpointConfiguratorProxy $endpointConfigProxy,
        callable $onPrepared = null,
        callable $onStarted = null,
        $isRunInBackground = true
    ) {
        $endpointConfigProxyFqcn = get_class($endpointConfigProxy);
        $this->endpointBehaviors[$endpointConfigProxyFqcn] = new EndpointBehavior(
            $endpointConfigProxy, $onPrepared, $onStarted, $isRunInBackground
        );

        return $this;
    }

    public function build()
    {
        $pipeSynchronizers = [];
        foreach ($this->endpointBehaviors as $endpointBehavior) {
            $endpointConfigProxyFqcn = get_class($endpointBehavior->getEndpointConfigProxy());
            $pipeSynchronizers[$endpointConfigProxyFqcn] = new PipeSynchronizer($endpointConfigProxyFqcn);
        }

        return new RunnableScenario(
            $this->contextFqcn,
            $this->endpointBehaviors,
            $pipeSynchronizers,
            $this->executionConfigurators
        );
    }

    /**
     * @param int $timeoutSeconds
     *
     * @return ScenarioRunResult
     */
    public function run($timeoutSeconds = 50)
    {
        $this->scenario = $this->build();
        return $this->scenario->run($timeoutSeconds);
    }

    /**
     * @return RunnableScenario
     */
    public function getScenario()
    {
        return $this->scenario;
    }
}
