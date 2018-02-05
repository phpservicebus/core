<?php

namespace spec\PSB\Core\Pipeline\Incoming\StageContext;

use PhpSpec\ObjectBehavior;

use PSB\Core\EndpointControlToken;
use PSB\Core\OutgoingOptionsFactory;
use PSB\Core\Pipeline\BusOperations;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingPhysicalMessageContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\Transport\IncomingPhysicalMessage;

/**
 * @mixin IncomingPhysicalMessageContext
 */
class IncomingPhysicalMessageContextSpec extends ObjectBehavior
{
    /**
     * @var IncomingPhysicalMessage
     */
    private $physicalMessageMock;
    private $pendingTransportOperationsMock;
    private $endpointControlTokenMock;

    function let(
        IncomingPhysicalMessage $physicalMessage,
        PipelineStageContext $parentContext,
        PendingTransportOperations $pendingTransportOperations,
        BusOperations $busOperations,
        OutgoingOptionsFactory $outgoingOptionsFactory,
        EndpointControlToken $endpointControlToken
    ) {
        $this->physicalMessageMock = $physicalMessage;
        $this->pendingTransportOperationsMock = $pendingTransportOperations;
        $this->endpointControlTokenMock = $endpointControlToken;

        $this->beConstructedWith(
            $physicalMessage,
            'id',
            [],
            $pendingTransportOperations,
            $busOperations,
            $outgoingOptionsFactory,
            $endpointControlToken,
            $parentContext
        );
    }

    function it_contains_the_message_set_at_construction()
    {
        $this->getMessage()->shouldReturn($this->physicalMessageMock);
    }

    function it_contains_the_message_id_from_the_message()
    {
        $this->getMessageId()->shouldReturn('id');
    }

    function it_contains_the_headers_from_the_message()
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
}
