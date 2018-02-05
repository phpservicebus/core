<?php

namespace spec\PSB\Core\Pipeline\Incoming\StageContext;

use PhpSpec\ObjectBehavior;

use PSB\Core\EndpointControlToken;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\OutgoingOptionsFactory;
use PSB\Core\Pipeline\BusOperations;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessage;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingLogicalMessageContext;
use PSB\Core\Pipeline\Incoming\StageContext\InvokeHandlerContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Transport\IncomingPhysicalMessage;

/**
 * @mixin InvokeHandlerContext
 */
class InvokeHandlerContextSpec extends ObjectBehavior
{
    /**
     * @var MessageHandlerInterface
     */
    private $messageHandlerMock;

    /**
     * @var IncomingLogicalMessage
     */
    private $messageBeingHandledMock;
    private $incomingPhysicalMessageMock;
    private $pendingTransportOperationsMock;
    private $endpointControlTokenMock;

    function let(
        MessageHandlerInterface $messageHandler,
        $messageBeingHandled,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $pendingTransportOperations,
        BusOperations $busOperations,
        OutgoingOptionsFactory $outgoingOptionsFactory,
        EndpointControlToken $endpointControlToken,
        IncomingLogicalMessageContext $parentContext
    ) {
        $this->messageHandlerMock = $messageHandler;
        $this->messageBeingHandledMock = $messageBeingHandled;
        $this->incomingPhysicalMessageMock = $incomingPhysicalMessage;
        $this->pendingTransportOperationsMock = $pendingTransportOperations;
        $this->endpointControlTokenMock = $endpointControlToken;

        $this->beConstructedWith(
            $messageHandler,
            $messageBeingHandled,
            'id',
            [],
            $incomingPhysicalMessage,
            $pendingTransportOperations,
            $busOperations,
            $outgoingOptionsFactory,
            $endpointControlToken,
            $parentContext
        );
    }

    function it_contains_the_handler_set_at_construction()
    {
        $this->getMessageHandler()->shouldReturn($this->messageHandlerMock);
    }

    function it_contains_the_message_being_handled_set_at_construction()
    {
        $this->getMessageBeingHandled()->shouldReturn($this->messageBeingHandledMock);
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
        $this->getIncomingPhysicalMessage()->shouldReturn($this->incomingPhysicalMessageMock);
    }

    function it_contains_the_transport_operations_from_the_parent_context()
    {
        $this->getPendingTransportOperations()->shouldReturn($this->pendingTransportOperationsMock);
    }

    function it_contains_the_endpoint_control_token_from_the_parent_context()
    {
        $this->getEndpointControlToken()->shouldReturn($this->endpointControlTokenMock);
    }

    function it_can_abort_dispatching_to_other_handlers()
    {
        $this->doNotContinueDispatchingCurrentMessageToHandlers();

        $this->isHandlerInvocationAborted()->shouldReturn(true);
    }
}
