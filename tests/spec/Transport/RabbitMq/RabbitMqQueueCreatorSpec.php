<?php

namespace spec\PSB\Core\Transport\RabbitMq;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Transport\QueueBindings;
use PSB\Core\Transport\RabbitMq\BrokerModel;
use PSB\Core\Transport\RabbitMq\RabbitMqQueueCreator;
use PSB\Core\Transport\RabbitMq\RoutingTopology;

/**
 * @mixin RabbitMqQueueCreator
 */
class RabbitMqQueueCreatorSpec extends ObjectBehavior
{
    function it_is_initializable(BrokerModel $brokerModel, RoutingTopology $routingTopology)
    {
        $this->beConstructedWith($brokerModel, $routingTopology, true);
        $this->shouldHaveType('PSB\Core\Transport\RabbitMq\RabbitMqQueueCreator');
    }

    function it_uses_the_routing_topology_to_create_queues_for_all_bound_addresses(
        BrokerModel $brokerModel,
        RoutingTopology $routingTopology,
        $useDurableMessages,
        QueueBindings $queueBindings
    ) {
        $this->beConstructedWith($brokerModel, $routingTopology, $useDurableMessages);
        $queueBindings->getSendingAddresses()->willReturn(['address1']);
        $queueBindings->getReceivingAddresses()->willReturn(['address2']);

        $routingTopology->setupForEndpointUse($brokerModel, 'address1')->shouldBeCalled();
        $routingTopology->setupForEndpointUse($brokerModel, 'address2')->shouldBeCalled();

        $this->createIfNecessary($queueBindings);
    }

    function it_does_not_create_any_queues_if_no_addresses_are_bound(
        BrokerModel $brokerModel,
        RoutingTopology $routingTopology,
        $useDurableMessages,
        QueueBindings $queueBindings
    ) {
        $this->beConstructedWith($brokerModel, $routingTopology, $useDurableMessages);
        $queueBindings->getSendingAddresses()->willReturn([]);
        $queueBindings->getReceivingAddresses()->willReturn([]);

        $routingTopology->setupForEndpointUse(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->createIfNecessary($queueBindings);
    }
}
