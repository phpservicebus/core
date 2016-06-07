<?php
namespace acceptancesupport\PSB\Core\Scenario;


use acceptancesupport\PSB\Core\Fork\Util\Error;

class ScenarioRunResult
{
    /**
     * @var ScenarioContext
     */
    private $scenarioContext;

    /**
     * @var Error[]
     */
    private $errors = [];

    /**
     * @var string[]
     */
    private $outputs = [];

    /**
     * @var bool[]
     */
    private $hangs = [];

    /**
     * @var EndpointQueueStats[]
     */
    private $queueStats = [];

    /**
     * @param ScenarioContext      $scenarioContext
     * @param array                $errors
     * @param array                $outputs
     * @param array                $hangs
     * @param EndpointQueueStats[] $queueStats
     */
    public function __construct(
        ScenarioContext $scenarioContext,
        array $errors,
        array $outputs,
        array $hangs,
        array $queueStats
    ) {
        $this->scenarioContext = $scenarioContext;
        $this->errors = $errors;
        $this->outputs = $outputs;
        $this->hangs = $hangs;
        $this->queueStats = $queueStats;
    }

    /**
     * @return ScenarioContext
     */
    public function getScenarioContext()
    {
        return $this->scenarioContext;
    }

    /**
     * @param string $endpointFqcn
     *
     * @return Error
     */
    public function getErrorFor($endpointFqcn)
    {
        return $this->errors[$endpointFqcn];
    }

    /**
     * @param string $endpointFqcn
     *
     * @return string
     */
    public function getOutputFor($endpointFqcn)
    {
        return $this->outputs[$endpointFqcn];
    }

    /**
     * @param string $endpointFqcn
     *
     * @return bool
     */
    public function hasHanged($endpointFqcn)
    {
        return $this->hangs[$endpointFqcn];
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return array_reduce(
            $this->errors,
            function ($carry, $item) {
                return $carry || $item !== null;
            },
            false
        );
    }

    /**
     * @param string $endpointFqcn
     *
     * @return int
     */
    public function getMessageCountInQueueOf($endpointFqcn)
    {
        return $this->queueStats[$endpointFqcn]->getMainCount();
    }

    /**
     * @param string $endpointFqcn
     *
     * @return int
     */
    public function getErrorMessageCountInQueueOf($endpointFqcn)
    {
        return $this->queueStats[$endpointFqcn]->getErrorCount();
    }
}
