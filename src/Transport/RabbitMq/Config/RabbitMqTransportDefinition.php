<?php
namespace PSB\Core\Transport\RabbitMq\Config;


use AMQPConnection;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\Transport\Config\TransportDefinition;
use PSB\Core\Transport\RabbitMq\AmqpConnectionFactory;
use PSB\Core\Transport\RabbitMq\BrokerModel;
use PSB\Core\Transport\RabbitMq\RoutingTopology;
use PSB\Core\Transport\TransportConnectionFactoryInterface;
use PSB\Core\Util\Settings;

class RabbitMqTransportDefinition extends TransportDefinition
{
    /**
     * @param Settings $settings
     *
     * @return RabbitMqTransportConfigurator
     */
    public function createConfigurator(Settings $settings)
    {
        return new RabbitMqTransportConfigurator($settings);
    }

    /**
     * Creates the TransportConnectionFactory specific to the transport implementation.
     *
     * @param Settings $settings
     *
     * @return AmqpConnectionFactory
     */
    public function createConnectionFactory(Settings $settings)
    {
        return new AmqpConnectionFactory($settings);
    }

    /**
     * @param Settings                            $settings
     * @param TransportConnectionFactoryInterface $connectionFactory
     *
     * @return RabbitMqTransportInfrastructure
     */
    public function formalize(Settings $settings, TransportConnectionFactoryInterface $connectionFactory)
    {
        $durableMessagingEnabled = $this->isDurableMessagingEnabled($settings);
        /** @var AMQPConnection $connection */
        $connection = $connectionFactory->createConnection();
        return new RabbitMqTransportInfrastructure(
            $settings,
            new RoutingTopology($durableMessagingEnabled),
            new BrokerModel($connection),
            $durableMessagingEnabled
        );
    }

    /**
     * @param Settings $settings
     *
     * @return bool
     */
    private function isDurableMessagingEnabled(Settings $settings)
    {
        $enabled = $settings->tryGet(KnownSettingsEnum::DURABLE_MESSAGING_ENABLED);

        if ($enabled === null) {
            $enabled = true;
        }

        return $enabled;
    }
}
