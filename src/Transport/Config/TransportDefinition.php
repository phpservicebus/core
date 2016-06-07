<?php
namespace PSB\Core\Transport\Config;


use PSB\Core\Transport\TransportConnectionFactoryInterface;
use PSB\Core\Util\Settings;

abstract class TransportDefinition
{
    /**
     * Creates the TransportConfigurator specific to the transport implementation.
     *
     * @param Settings $settings
     *
     * @return TransportConfigurator
     */
    abstract public function createConfigurator(Settings $settings);

    /**
     * Creates the TransportConnectionFactory specific to the transport implementation.
     *
     * @param Settings $settings
     *
     * @return TransportConnectionFactoryInterface
     */
    abstract public function createConnectionFactory(Settings $settings);

    /**
     * This is where subclasses initialize the factories and supported features for the transport.
     * This method is called right before all features are activated and the settings will be locked down.
     *
     * @param Settings                            $settings
     * @param TransportConnectionFactoryInterface $connectionFactory
     *
     * @return TransportInfrastructure
     */
    abstract public function formalize(Settings $settings, TransportConnectionFactoryInterface $connectionFactory);
}
