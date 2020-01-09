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

    public function startTest(Test $test): void
    {
        /** @var ScenarioTestCase $test */
        $test->addEndpointTestExecutionConfigurator($this->configurator);
    }

    public function addError(Test $test, \Throwable $t, float $time): void
    {
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
    }

    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
    }

    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
    }

    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
    }

    public function startTestSuite(TestSuite $suite): void
    {
    }

    public function endTestSuite(TestSuite $suite): void
    {
    }

    public function endTest(Test $test, float $time): void
    {
    }
}
