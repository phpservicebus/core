<?php

namespace acceptancesupport\PSB\Core\PhpUnit;


use acceptancesupport\PSB\Core\Scenario\EndpointTestExecutionConfiguratorInterface;
use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;

class TestExecutionConfiguratorLoader implements TestListener
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

    public function startTest(Test $test)
    {
        /** @var ScenarioTestCase $test */
        $test->addEndpointTestExecutionConfigurator($this->configurator);
    }

    public function endTest(Test $test, $time)
    {
    }

    public function addError(Test $test, Exception $e, $time)
    {
    }

    public function addWarning(Test $test, Warning $e, $time)
    {
    }

    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
    }

    public function addIncompleteTest(Test $test, Exception $e, $time)
    {
    }

    public function addRiskyTest(Test $test, Exception $e, $time)
    {
    }

    public function addSkippedTest(Test $test, Exception $e, $time)
    {
    }

    public function startTestSuite(TestSuite $suite)
    {
    }

    public function endTestSuite(TestSuite $suite)
    {
    }
}
