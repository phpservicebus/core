<?php

namespace spec\PSB\Core\Transport\RabbitMq;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Transport\RabbitMq\AmqpConnectionFactory;
use PSB\Core\Transport\RabbitMq\RabbitMqKnownSettingsEnum;
use PSB\Core\Util\Settings;

/**
 * @mixin AmqpConnectionFactory
 */
class AmqpConnectionFactorySpec extends ObjectBehavior
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
        $this->shouldHaveType('PSB\Core\Transport\RabbitMq\AmqpConnectionFactory');
    }

    function it_creates_by_returning_the_connection_from_settings_if_it_is_set(\AMQPConnection $connection)
    {
        $this->settingsMock->tryGet(RabbitMqKnownSettingsEnum::CONNECTION)->willReturn($connection);

        $this->createConnection()->shouldReturn($connection);
    }

    function it_creates_a_new_connection_by_merging_defaults_with_connection_credentials_found_in_settings()
    {
        $this->settingsMock->tryGet(RabbitMqKnownSettingsEnum::CONNECTION)->willReturn(null);
        $this->settingsMock->tryGet(RabbitMqKnownSettingsEnum::CONNECTION_CREDENTIALS)->willReturn(null);

        $this->createConnection()->shouldHaveType('\AMQPConnection');
    }
}
