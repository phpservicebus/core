<?php

namespace spec\PSB\Core\Pipeline\Incoming;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Outbox\OutboxMessage;
use PSB\Core\Outbox\OutboxTransportOperation;
use PSB\Core\Outbox\OutboxTransportOperationFactory;
use PSB\Core\Pipeline\Incoming\TransportOperationsConverter;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Routing\MulticastAddressTag;
use PSB\Core\Routing\UnicastAddressTag;
use PSB\Core\Transport\OutgoingPhysicalMessage;
use PSB\Core\Transport\TransportOperation;
use spec\PSB\Core\Pipeline\Incoming\TransportOperationsConverterSpec\UnconvertibleAddressTag;

/**
 * @mixin TransportOperationsConverter
 */
class TransportOperationsConverterSpec extends ObjectBehavior
{
    /**
     * @var OutboxTransportOperationFactory
     */
    private $outboxOperationFactoryMock;

    function let(OutboxTransportOperationFactory $outboxOperationFactory)
    {
        $this->outboxOperationFactoryMock = $outboxOperationFactory;
        $this->beConstructedWith($outboxOperationFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Incoming\TransportOperationsConverter');
    }

    function it_converts_from_transport_operations_to_outbox_operations(PendingTransportOperations $transportOperations)
    {
        $outgoingMessage1 = new OutgoingPhysicalMessage('id1', [], 'body');
        $outgoingMessage2 = new OutgoingPhysicalMessage('id2', [], 'body');
        $transportOperations->getOperations()->willReturn(
            [
                new TransportOperation($outgoingMessage1, new UnicastAddressTag('queuename')),
                new TransportOperation($outgoingMessage2, new MulticastAddressTag('MessageType'))
            ]
        );

        $outboxOperation1 = new OutboxTransportOperation('id1', ['destination' => 'queuename'], 'body', []);
        $outboxOperation2 = new OutboxTransportOperation('id2', ['message_type' => 'MessageType'], 'body', []);
        $this->outboxOperationFactoryMock->create($outgoingMessage1, ['destination' => 'queuename'])->willReturn(
            $outboxOperation1
        );
        $this->outboxOperationFactoryMock->create($outgoingMessage2, ['message_type' => 'MessageType'])->willReturn(
            $outboxOperation2
        );

        $this->convertToOutboxOperations($transportOperations)->shouldReturn([$outboxOperation1, $outboxOperation2]);
    }

    function it_throws_if_address_tag_cannot_be_converted_from_transport_to_outbox(
        PendingTransportOperations $transportOperations
    ) {
        $outgoingMessage = new OutgoingPhysicalMessage('id1', [], 'body');
        $transportOperations->getOperations()->willReturn(
            [
                new TransportOperation($outgoingMessage, new UnconvertibleAddressTag()),
            ]
        );

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringConvertToOutboxOperations(
            $transportOperations
        );
    }

    function it_converts_from_outbox_operations_to_transport_operations(OutboxMessage $outboxMessage)
    {
        $outboxOperation1 = new OutboxTransportOperation('id1', ['destination' => 'queuename'], 'body', []);
        $outboxOperation2 = new OutboxTransportOperation('id2', ['message_type' => 'MessageType'], 'body', []);
        $outboxMessage->getTransportOperations()->willReturn([$outboxOperation1, $outboxOperation2]);

        $outgoingMessage1 = new OutgoingPhysicalMessage('id1', [], 'body');
        $outgoingMessage2 = new OutgoingPhysicalMessage('id2', [], 'body');
        $transportOperation1 = new TransportOperation($outgoingMessage1, new UnicastAddressTag('queuename'));
        $transportOperation2 = new TransportOperation($outgoingMessage2, new MulticastAddressTag('MessageType'));
        $pendingTransportOperations = new PendingTransportOperations();
        $pendingTransportOperations->add($transportOperation1);
        $pendingTransportOperations->add($transportOperation2);

        $this->convertToPendingTransportOperations($outboxMessage)->shouldBeLike($pendingTransportOperations);
    }

    function it_throws_if_address_tag_cannot_be_converted_from_outbox_to_transport(OutboxMessage $outboxMessage)
    {
        $outboxOperation = new OutboxTransportOperation('id1', ['garbage' => ''], 'body', []);
        $outboxMessage->getTransportOperations()->willReturn([$outboxOperation]);

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringConvertToPendingTransportOperations(
            $outboxMessage
        );
    }
}

namespace spec\PSB\Core\Pipeline\Incoming\TransportOperationsConverterSpec;

use PSB\Core\Routing\AddressTagInterface;

class UnconvertibleAddressTag implements AddressTagInterface
{

}
