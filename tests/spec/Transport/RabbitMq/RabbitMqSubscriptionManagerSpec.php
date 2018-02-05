<?php

namespace spec\PSB\Core\Transport\RabbitMq;

use PhpSpec\ObjectBehavior;

use PSB\Core\Transport\RabbitMq\BrokerModel;
use PSB\Core\Transport\RabbitMq\RabbitMqSubscriptionManager;
use PSB\Core\Transport\RabbitMq\RoutingTopology;

/**
 * @mixin RabbitMqSubscriptionManager
 */
class RabbitMqSubscriptionManagerSpec extends ObjectBehavior
{
    /**
     * @var BrokerModel
     */
    private $brokerModelMock;

    /**
     * @var RoutingTopology
     */
    private $routingTopologyMock;

    private $localQueue = 'irrelevantqueue';

    function let(BrokerModel $brokerModel, RoutingTopology $routingTopology)
    {
        $this->brokerModelMock = $brokerModel;
        $this->routingTopologyMock = $routingTopology;
        $this->beConstructedWith($brokerModel, $routingTopology, $this->localQueue);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\RabbitMq\RabbitMqSubscriptionManager');
    }

    function it_subscribes_to_an_event()
    {
        $eventFqcn = 'irrelevantevent';

        $this->routingTopologyMock->setupSubscription(
            $this->brokerModelMock,
            $eventFqcn,
            $this->localQueue
        )->shouldBeCalled();

        $this->subscribe($eventFqcn);
    }

    function it_unsubscribes_from_an_event()
    {
        $eventFqcn = 'irrelevantevent';

        $this->routingTopologyMock->tearDownSubscription(
            $this->brokerModelMock,
            $eventFqcn,
            $this->localQueue
        )->shouldBeCalled();

        $this->unsubscribe($eventFqcn);
    }
}
