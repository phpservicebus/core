<?php
namespace acceptancesupport\PSB\Core\Scenario;


class RunContext
{
    /**
     * @var string
     */
    private $endpointConfigProxyFqcn;

    /**
     * @var PipeSynchronizer[]
     */
    private $pipeSynchronizers;

    /**
     * @var ScenarioContextProxy
     */
    private $scenarioContext;

    /**
     * @param string               $endpointConfigProxyFqcn
     * @param PipeSynchronizer[]   $pipeSynchronizers
     * @param ScenarioContextProxy $scenarioContext
     */
    public function __construct(
        $endpointConfigProxyFqcn,
        array $pipeSynchronizers,
        ScenarioContextProxy $scenarioContext
    ) {
        $this->endpointConfigProxyFqcn = $endpointConfigProxyFqcn;
        $this->pipeSynchronizers = $pipeSynchronizers;
        $this->scenarioContext = $scenarioContext;
    }

    /**
     * Synchronization method to be used by endpoints to wait for other endpoints.
     *
     * @param string $endpointBuilderFqcn
     */
    public function waitForGoFrom($endpointBuilderFqcn)
    {
        $this->pipeSynchronizers[$endpointBuilderFqcn]->waitForGo();
    }

    /**
     * Sync method to be used by endpoints to notify other endpoints that they no longer need to wait.
     */
    public function go()
    {
        $this->pipeSynchronizers[$this->endpointConfigProxyFqcn]->go();
    }

    /**
     * @return mixed
     */
    public function getScenarioContext()
    {
        return $this->scenarioContext;
    }
}
