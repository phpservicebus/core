<?php
namespace acceptancesupport\PSB\Core\Scenario;


use PSB\Core\EndpointConfigurator;

/**
 * @mixin EndpointConfigurator
 * @property EndpointConfigurator $configurator
 */
abstract class EndpointConfiguratorProxy
{
    public $scenarioContext;

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->configurator, $method), $args);
    }

    public function __get($name)
    {
        if ($name == 'configurator') {
            $configurator = EndpointConfigurator::create(get_class($this));
            $configurator->sendFailedMessagesTo(get_class($this) . '.Error');
            $configurator->enableInstallers();
            $this->configurator = $configurator;

            return $configurator;
        }

        return $this->$name;
    }

    /**
     * @return EndpointConfigurator
     */
    public function getConfigurator()
    {
        return $this->configurator;
    }

    abstract public function init();
}
