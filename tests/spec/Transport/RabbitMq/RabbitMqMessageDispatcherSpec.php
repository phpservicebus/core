<?php

namespace spec\PSB\Core\Transport\RabbitMq;

use PhpSpec\ObjectBehavior;
use PSB\Core\Routing\MulticastAddressTag;
use PSB\Core\Routing\UnicastAddressTag;
use PSB\Core\Transport\OutgoingPhysicalMessage;
use PSB\Core\Transport\RabbitMq\BrokerModel;
use PSB\Core\Transport\RabbitMq\MessageConverter;
use PSB\Core\Transport\RabbitMq\RabbitMqMessageDispatcher;
use PSB\Core\Transport\RabbitMq\RoutingTopology;
use PSB\Core\Transport\TransportOperation;
use PSB\Core\Transport\TransportOperations;
use spec\PSB\Core\Transport\RabbitMq\RabbitMqMessageDispatcherSpec\UnsupportedAddressTag;

/**
 * @mixin RabbitMqMessageDispatcher
 */
class RabbitMqMessageDispatcherSpec extends ObjectBehavior
{
    /**
     * @var RoutingTopology
     */
    private $routingTopologyMock;

    /**
     * @var BrokerModel
     */
    private $brokerModelMock;

    /**
     * @var MessageConverter
     */
    private $messageConverterMock;

    function let(
        RoutingTopology $routingTopology,
        BrokerModel $brokerModel,
        MessageConverter $messageConverter
    ) {
        $this->routingTopologyMock = $routingTopology;
        $this->brokerModelMock = $brokerModel;
        $this->messageConverterMock = $messageConverter;
        $this->beConstructedWith($routingTopology, $brokerModel, $messageConverter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\RabbitMq\RabbitMqMessageDispatcher');
    }

    function it_dispatches_unicast_messages(TransportOperations $transportOperations)
    {
        $messageId = 'irrelevantid';
        $messageBody = 'irrelevantbody';
        $messageAddress = 'irrelevantaddress';
        $transportOperations->getTransportOperations()->willReturn(
            $this->buildUnicastTransportOperations($messageId, [], $messageBody, $messageAddress)
        );
        $this->messageConverterMock->composeRabbitMqAttributes(
            new OutgoingPhysicalMessage($messageId, [], $messageBody)
        )->willReturn([]);

        $this->routingTopologyMock->send($this->brokerModelMock, $messageAddress, $messageBody, [])->shouldBeCalled();

        $this->dispatch($transportOperations);
    }

    function it_dispatches_multicast_messages(TransportOperations $transportOperations)
    {
        $messageId = 'irrelevantid';
        $messageBody = 'irrelevantbody';
        $mesasgeType = 'irrelevanttype';
        $transportOperations->getTransportOperations()->willReturn(
            $this->buildMulticastTransportOperations($messageId, [], $messageBody, $mesasgeType)
        );
        $this->messageConverterMock->composeRabbitMqAttributes(
            new OutgoingPhysicalMessage($messageId, [], $messageBody)
        )->willReturn([]);

        $this->routingTopologyMock->publish($this->brokerModelMock, $mesasgeType, $messageBody, [])->shouldBeCalled();

        $this->dispatch($transportOperations);
    }

    function it_throws_if_address_tag_is_unsupported(TransportOperations $transportOperations)
    {
        $messageId = 'irrelevantid';
        $messageBody = 'irrelevantbody';
        $transportOperations->getTransportOperations()->willReturn(
            [
                new TransportOperation(
                    new OutgoingPhysicalMessage($messageId, [], $messageBody),
                    new UnsupportedAddressTag()
                )
            ]
        );

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringDispatch($transportOperations);
    }

    private function buildUnicastTransportOperations($messageId, array $headers, $body, $address)
    {
        return $this->buildTransportOperations(true, $messageId, $headers, $body, $address);
    }

    private function buildMulticastTransportOperations($messageId, array $headers, $body, $address)
    {
        return $this->buildTransportOperations(false, $messageId, $headers, $body, $address);
    }

    private function buildTransportOperations($isUnicast, $messageId, array $headers, $body, $address)
    {
        return [
            new TransportOperation(
                new OutgoingPhysicalMessage($messageId, $headers, $body),
                $isUnicast ? new UnicastAddressTag($address) : new MulticastAddressTag($address)
            ),
            new TransportOperation(
                new OutgoingPhysicalMessage($messageId, $headers, $body),
                $isUnicast ? new UnicastAddressTag($address) : new MulticastAddressTag($address)
            )
        ];
    }
}
