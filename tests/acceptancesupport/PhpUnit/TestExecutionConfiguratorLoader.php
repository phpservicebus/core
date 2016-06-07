<?php
namespace acceptancesupport\PSB\Core\PhpUnit;


use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\EndpointTestExecutionConfiguratorInterface;
use Exception;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestSuite;

class TestExecutionConfiguratorLoader implements \PHPUnit_Framework_TestListener
{
    /**
     * @var EndpointTestExecutionConfiguratorInterface
     */
    private $configurator;

    public function __construct()
    {
        $params = func_get_args();
        $configuratorFqcn = array_shift($params);

        $instantiator = new \ReflectionClass($configuratorFqcn);
        if (null !== $instantiator->getConstructor() && $instantiator->isInstantiable()) {
            $this->configurator = $instantiator->newInstanceArgs($params);
        } else {
            $this->configurator = $instantiator->newInstanceWithoutConstructor();
        }

    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
        /** @var ScenarioTestCase $test */
        $test->addEndpointTestExecutionConfigurator($this->configurator);
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
    }

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    }

    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }
}
