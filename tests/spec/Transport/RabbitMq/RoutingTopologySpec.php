<?php

namespace spec\PSB\Core\Transport\RabbitMq;

use PhpSpec\ObjectBehavior;
use PSB\Core\Transport\RabbitMq\BrokerModel;
use PSB\Core\Transport\RabbitMq\RoutingTopology;

/**
 * @mixin RoutingTopology
 */
class RoutingTopologySpec extends ObjectBehavior
{
    private $useDurableMessaging = true;

    function let()
    {
        $this->beConstructedWith($this->useDurableMessaging);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\RabbitMq\RoutingTopology');
    }

    function it_declares_and_binds_the_queue_and_exchange_used_by_an_endpoint(BrokerModel $broker)
    {
        $queueName = 'irrelevantqueue';
        $broker->declareQueue($queueName, AMQP_DURABLE)->shouldBeCalled();
        $broker->declareExchange($queueName, AMQP_EX_TYPE_FANOUT, AMQP_DURABLE)->shouldBeCalled();
        $broker->bindQueue($queueName, $queueName, '')->shouldBeCalled();

        $this->setupForEndpointUse($broker, $queueName);
    }

    function it_declares_and_binds_the_sanitized_name_of_the_queue_and_exchange_used_by_an_endpoint(BrokerModel $broker)
    {
        $unsafeQueueName = 'irre/levant queue';
        $safeQueueName = 'irre.levant.queue';
        $broker->declareQueue($safeQueueName, AMQP_DURABLE)->shouldBeCalled();
        $broker->declareExchange($safeQueueName, AMQP_EX_TYPE_FANOUT, AMQP_DURABLE)->shouldBeCalled();
        $broker->bindQueue($safeQueueName, $safeQueueName, '')->shouldBeCalled();

        $this->setupForEndpointUse($broker, $unsafeQueueName);
    }

    function it_subscribes_by_creating_exchanges_for_the_message_and_its_interfaces_and_binds_the_endpoint_exchange_to_the_message_exchange(
        BrokerModel $broker
    ) {
        $messageFqcn = 'spec\PSB\Core\Transport\RabbitMq\RoutingTopologySpec\Message';
        $subscriberName = 'irrelevantname';

        $broker->declareExchange(
            'spec.PSB.Core.Transport.RabbitMq.RoutingTopologySpec:Message',
            AMQP_EX_TYPE_FANOUT,
            AMQP_DURABLE
        )->shouldBeCalled();
        $broker->declareExchange(
            'spec.PSB.Core.Transport.RabbitMq.RoutingTopologySpec:MessageInterface',
            AMQP_EX_TYPE_FANOUT,
            AMQP_DURABLE
        )->shouldBeCalled();
        $broker->bindExchange(
            'spec.PSB.Core.Transport.RabbitMq.RoutingTopologySpec:MessageInterface',
            'spec.PSB.Core.Transport.RabbitMq.RoutingTopologySpec:Message'
        )->shouldBeCalled();
        $broker->bindExchange(
            $subscriberName,
            'spec.PSB.Core.Transport.RabbitMq.RoutingTopologySpec:Message'
        )->shouldBeCalled();

        $this->setupSubscription($broker, $messageFqcn, $subscriberName);
    }

    function it_unsubscribes_by_unbinding_the_endpoint_exchange_from_the_message_exchange(BrokerModel $broker)
    {
        $messageFqcn = 'spec\PSB\Core\Transport\RabbitMq\RoutingTopologySpec\Message';
        $subscriberName = 'irrelevantname';

        $broker->unbindExchange(
            $subscriberName,
            'spec.PSB.Core.Transport.RabbitMq.RoutingTopologySpec:Message'
        )->shouldBeCalled();

        $this->tearDownSubscription($broker, $messageFqcn, $subscriberName);
    }

    function it_publishes_to_the_message_exchange_after_it_creates_the_exchanges_for_the_message_and_its_interfaces(
        BrokerModel $broker
    ) {
        $messageFqcn = 'spec\PSB\Core\Transport\RabbitMq\RoutingTopologySpec\Message';
        $messageBody = 'irrelevantbody';

        $broker->declareExchange(
            'spec.PSB.Core.Transport.RabbitMq.RoutingTopologySpec:Message',
            AMQP_EX_TYPE_FANOUT,
            AMQP_DURABLE
        )->shouldBeCalled();
        $broker->declareExchange(
            'spec.PSB.Core.Transport.RabbitMq.RoutingTopologySpec:MessageInterface',
            AMQP_EX_TYPE_FANOUT,
            AMQP_DURABLE
        )->shouldBeCalled();
        $broker->bindExchange(
            'spec.PSB.Core.Transport.RabbitMq.RoutingTopologySpec:MessageInterface',
            'spec.PSB.Core.Transport.RabbitMq.RoutingTopologySpec:Message'
        )->shouldBeCalled();
        $broker->publish(
            'spec.PSB.Core.Transport.RabbitMq.RoutingTopologySpec:Message',
            $messageBody,
            '',
            AMQP_NOPARAM,
            ['delivery_mode' => 2]
        )->shouldBeCalled();

        $this->publish($broker, $messageFqcn, $messageBody);
    }

    function it_sends_by_publishing_on_the_recipient_endpoint_exchange(BrokerModel $broker)
    {
        $unsafeAddress = 'irrelevant address';
        $safeAddress = 'irrelevant.address';
        $messageBody = 'irrelevantbody';

        $broker->publish($safeAddress, $messageBody, '', AMQP_NOPARAM, ['delivery_mode' => 2])->shouldBeCalled();

        $this->send($broker, $unsafeAddress, $messageBody);
    }

    function it_sends_directly_to_a_queue_by_publishing_on_the_global_exchange_with_que_name_as_routing_key(
        BrokerModel $broker
    ) {
        $unsafeQueueName = 'irre/levant queue';
        $safeQueueName = 'irre.levant.queue';
        $messageBody = 'irrelevantbody';

        $broker->publish('', $messageBody, $safeQueueName, AMQP_NOPARAM, ['delivery_mode' => 2])->shouldBeCalled();

        $this->sendToQueue($broker, $unsafeQueueName, $messageBody);
    }
}
