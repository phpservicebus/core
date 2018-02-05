<?php
namespace acceptancesupport\PSB\Core\Scenario;

/**
 * It writes the wrapped context to disk with every property change using the context storage
 */
class ScenarioContextProxy
{
    /**
     * @var ScenarioContext
     */
    private $context;

    /**
     * @var ScenarioContextStorage
     */
    private $contextStorage;

    /**
     * @param ScenarioContext        $context
     * @param ScenarioContextStorage $contextStorage
     */
    public function __construct(ScenarioContext $context, ScenarioContextStorage $contextStorage)
    {
        $this->context = $context;
        $this->contextStorage = $contextStorage;
    }

    public function __call($method, $args)
    {
        return $this->synchronized(
            function ($context) use ($method, $args) {
                return call_user_func_array([$context, $method], $args);
            }
        );
    }

    public function __get($name)
    {
        $this->refreshFromStorage();
        return $this->context->$name;
    }

    public function __set($name, $value)
    {
        $this->synchronized(
            function ($context) use ($name, $value) {
                $context->$name = $value;
            }
        );
    }

    /**
     * @return ScenarioContext
     */
    public function getContext()
    {
        return $this->context;
    }

    public function cleanupStorage()
    {
        $this->contextStorage->cleanup();
    }

    public function refreshFromStorage()
    {
        $this->synchronized(
            function () {
            }
        );
    }

    private function synchronized(callable $callback)
    {
        $this->contextStorage->lock();
        $context = $this->contextStorage->read();
        if ($context) {
            $this->context = $context;
        }
        $return = $callback($this->context);
        $this->contextStorage->write($this);
        $this->contextStorage->unlock();
        return $return;
    }
}
