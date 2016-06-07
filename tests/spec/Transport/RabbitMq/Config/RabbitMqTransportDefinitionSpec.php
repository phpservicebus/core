<?php

namespace spec\PSB\Core\Transport\RabbitMq\Config;

use AMQPConnection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\Transport\RabbitMq\AmqpConnectionFactory;
use PSB\Core\Transport\RabbitMq\BrokerModel;
use PSB\Core\Transport\RabbitMq\Config\RabbitMqTransportConfigurator;
use PSB\Core\Transport\RabbitMq\Config\RabbitMqTransportDefinition;
use PSB\Core\Transport\RabbitMq\Config\RabbitMqTransportInfrastructure;
use PSB\Core\Transport\RabbitMq\RoutingTopology;
use PSB\Core\Transport\TransportConnectionFactoryInterface;
use PSB\Core\Util\Settings;

/**
 * @mixin RabbitMqTransportDefinition
 */
class RabbitMqTransportDefinitionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\RabbitMq\Config\RabbitMqTransportDefinition');
    }

    function it_creates_a_configurator(Settings $settings)
    {
        $this->createConfigurator($settings)->shouldBeLike(
            new RabbitMqTransportConfigurator($settings->getWrappedObject())
        );
    }

    function it_creates_a_connection_factory(Settings $settings)
    {
        $this->createConnectionFactory($settings)->shouldBeLike(
            new AmqpConnectionFactory($settings->getWrappedObject())
        );
    }

    function it_formalizes_by_creating_the_infrastructure_factories(
        Settings $settings,
        TransportConnectionFactoryInterface $connectionFactory,
        AMQPConnection $connection
    ) {
        $isDurableMessagingEnabled = false;
        $connectionFactory->createConnection()->willReturn($connection);
        $settings->tryGet(KnownSettingsEnum::DURABLE_MESSAGING_ENABLED)->willReturn($isDurableMessagingEnabled);
        $this->formalize($settings, $connectionFactory)->shouldBeLike(
            new RabbitMqTransportInfrastructure(
                $settings->getWrappedObject(),
                new RoutingTopology($isDurableMessagingEnabled),
                new BrokerModel($connection->getWrappedObject()),
                $isDurableMessagingEnabled
            )
        );
    }

    function it_formalizes_with_durable_messaging_if_not_explicitly_disabled(
        Settings $settings,
        TransportConnectionFactoryInterface $connectionFactory,
        AMQPConnection $connection
    ) {
        $isDurableMessagingEnabled = true;
        $connectionFactory->createConnection()->willReturn($connection);
        $settings->tryGet(KnownSettingsEnum::DURABLE_MESSAGING_ENABLED)->willReturn(null);
        $this->formalize($settings, $connectionFactory)->shouldBeLike(
            new RabbitMqTransportInfrastructure(
                $settings->getWrappedObject(),
                new RoutingTopology($isDurableMessagingEnabled),
                new BrokerModel($connection->getWrappedObject()),
                $isDurableMessagingEnabled
            )
        );
    }
}
