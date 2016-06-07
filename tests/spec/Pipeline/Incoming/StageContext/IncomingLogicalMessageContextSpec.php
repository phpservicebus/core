<?php

namespace spec\PSB\Core\Pipeline\Incoming\StageContext;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\EndpointControlToken;
use PSB\Core\OutgoingOptionsFactory;
use PSB\Core\Pipeline\BusOperations;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessage;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingLogicalMessageContext;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingPhysicalMessageContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Transport\IncomingPhysicalMessage;

/**
 * @mixin IncomingLogicalMessageContext
 */
class IncomingLogicalMessageContextSpec extends ObjectBehavior
{
    /**
     * @var IncomingLogicalMessage
     */
    private $logicalMessageMock;
    private $physicalMessageMock;
    private $pendingTransportOperationsMock;
    private $outgoingOptionsFactoryMock;
    private $endpointControlTokenMock;

    function let(
        IncomingLogicalMessage $logicalMessage,
        IncomingPhysicalMessage $physicalMessage,
        PendingTransportOperations $pendingTransportOperations,
        BusOperations $busOperations,
        OutgoingOptionsFactory $outgoingOptionsFactory,
        EndpointControlToken $endpointControlToken,
        IncomingPhysicalMessageContext $parentContext
    ) {
        $this->logicalMessageMock = $logicalMessage;
        $this->physicalMessageMock = $physicalMessage;
        $this->pendingTransportOperationsMock = $pendingTransportOperations;
        $this->outgoingOptionsFactoryMock = $outgoingOptionsFactory;
        $this->endpointControlTokenMock = $endpointControlToken;
        $this->beConstructedWith(
            $logicalMessage,
            'id',
            [],
            $physicalMessage,
            $pendingTransportOperations,
            $busOperations,
            $outgoingOptionsFactory,
            $endpointControlToken,
            $parentContext
        );
    }

    function it_contains_the_logical_message_set_at_construction()
    {
        $this->getMessage()->shouldReturn($this->logicalMessageMock);
    }

    function it_contains_the_message_id_from_the_parent_context()
    {
        $this->getMessageId()->shouldReturn('id');
    }

    function it_contains_the_headers_from_the_parent_context()
    {
        $this->getHeaders()->shouldReturn([]);
    }

    function it_contains_the_incoming_physical_message_from_the_parent_context()
    {
        $this->getIncomingPhysicalMessage()->shouldReturn($this->physicalMessageMock);
    }

    function it_contains_the_transport_operations_from_the_parent_context()
    {
        $this->getPendingTransportOperations()->shouldReturn($this->pendingTransportOperationsMock);
    }

    function it_contains_the_endpoint_control_token_from_the_parent_context()
    {
        $this->getEndpointControlToken()->shouldReturn($this->endpointControlTokenMock);
    }

    function it_can_mark_message_as_being_handled()
    {
        $this->markMessageAsHandled();

        $this->isMessageHandled()->shouldReturn(true);
    }
}
