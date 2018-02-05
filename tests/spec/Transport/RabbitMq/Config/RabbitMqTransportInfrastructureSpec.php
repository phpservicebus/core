<?php

namespace spec\PSB\Core\Transport\RabbitMq\Config;

use PhpSpec\ObjectBehavior;

use PSB\Core\Transport\Config\TransportReceiveInfrastructure;
use PSB\Core\Transport\Config\TransportSendInfrastructure;
use PSB\Core\Transport\Config\TransportSubscriptionInfrastructure;
use PSB\Core\Transport\RabbitMq\BrokerModel;
use PSB\Core\Transport\RabbitMq\Config\RabbitMqTransportInfrastructure;
use PSB\Core\Transport\RabbitMq\RoutingTopology;
use PSB\Core\Util\Settings;

/**
 * @mixin RabbitMqTransportInfrastructure
 */
class RabbitMqTransportInfrastructureSpec extends ObjectBehavior
{
    /**
     * @var Settings
     */
    private $settingsMock;
    /**
     * @var RoutingTopology
     */
    private $routingTopologyMock;
    /**
     * @var BrokerModel
     */
    private $brokerModelMock;

    private $durableMessagesEnabled = true;

    function let(
        Settings $settings,
        RoutingTopology $routingTopology,
        BrokerModel $brokerModel
    ) {
        $this->settingsMock = $settings;
        $this->routingTopologyMock = $routingTopology;
        $this->brokerModelMock = $brokerModel;
        $this->beConstructedWith($settings, $routingTopology, $brokerModel, $this->durableMessagesEnabled);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\RabbitMq\Config\RabbitMqTransportInfrastructure');
    }

    function it_configures_the_send_infrastructure()
    {
        $this->configureSendInfrastructure()->shouldHaveType(TransportSendInfrastructure::class);
    }

    function it_configures_the_receive_infrastructure()
    {
        $this->configureReceiveInfrastructure()->shouldHaveType(TransportReceiveInfrastructure::class);
    }

    function it_configures_the_subscription_infrastructure()
    {
        $this->configureSubscriptionInfrastructure()->shouldHaveType(TransportSubscriptionInfrastructure::class);
    }

    function it_returns_the_same_address_on_conversion_if_it_is_already_valid()
    {
        $address = 'ValidAddressContainingHyphens-Underscores_-Digits20Periods._And.Colons::_';
        $this->toTransportAddress($address)->shouldReturn($address);
    }

    function it_returns_a_new_address_in_which_illegal_characters_are_changed_to_dots_on_conversion()
    {
        $this->toTransportAddress('Illegal space')->shouldReturn('Illegal.space');
        $this->toTransportAddress('Illegal!/characters_\'mixed_with&^legal799ones')->shouldReturn(
            'Illegal..characters_.mixed_with..legal799ones'
        );
    }
}
