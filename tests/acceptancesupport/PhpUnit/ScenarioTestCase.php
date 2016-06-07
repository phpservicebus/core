<?php
namespace acceptancesupport\PSB\Core\PhpUnit;


use acceptancesupport\PSB\Core\Scenario\EndpointTestExecutionConfiguratorInterface;
use acceptancesupport\PSB\Core\Scenario\ScenarioBuilder;

abstract class ScenarioTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ScenarioBuilder
     */
    protected $scenario;

    /**
     * @var EndpointTestExecutionConfiguratorInterface[]
     */
    protected $executionConfigurators = [];

    public function setUp()
    {
        if (!$this->scenario) {
            $this->scenario = new ScenarioBuilder($this->executionConfigurators);
        }
    }

    /**
     * @param EndpointTestExecutionConfiguratorInterface $executionConfigurator
     */
    public function addEndpointTestExecutionConfigurator(
        EndpointTestExecutionConfiguratorInterface $executionConfigurator
    ) {
        $this->executionConfigurators[get_class($executionConfigurator)] = $executionConfigurator;
    }

    public function tearDown()
    {
        if ($this->scenario->getScenario()) {
            $this->scenario->getScenario()->cleanup();
        }
    }
}
