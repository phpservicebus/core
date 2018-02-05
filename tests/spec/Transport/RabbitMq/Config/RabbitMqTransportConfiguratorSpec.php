<?php

namespace spec\PSB\Core\Transport\RabbitMq\Config;

use PhpSpec\ObjectBehavior;

use PSB\Core\Transport\RabbitMq\Config\RabbitMqTransportConfigurator;
use PSB\Core\Transport\RabbitMq\RabbitMqKnownSettingsEnum;
use PSB\Core\Util\Settings;

/**
 * @mixin RabbitMqTransportConfigurator
 */
class RabbitMqTransportConfiguratorSpec extends ObjectBehavior
{
    /**
     * @var Settings
     */
    private $settingsMock;

    function let(Settings $settings)
    {
        $this->settingsMock = $settings;
        $this->beConstructedWith($settings);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\RabbitMq\Config\RabbitMqTransportConfigurator');
    }

    function it_uses_amqp_connection_credentials(){
        $this->settingsMock->set(
            RabbitMqKnownSettingsEnum::CONNECTION_CREDENTIALS,
            ['some' => 'param']
        )->shouldBeCalled();

        $this->useConnectionCredentials(['some' => 'param'])->shouldReturn($this);
    }

    function it_uses_amqp_connection(\AMQPConnection $connection)
    {
        $this->settingsMock->set(
            RabbitMqKnownSettingsEnum::CONNECTION,
            $connection
        )->shouldBeCalled();

        $this->useConnection($connection)->shouldReturn($this);
    }
}
