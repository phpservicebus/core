<?php

namespace spec\PSB\Core\Pipeline\Incoming;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\EndpointControlToken;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\OutgoingOptionsFactory;
use PSB\Core\Pipeline\BusOperations;
use PSB\Core\Pipeline\Incoming\IncomingContextFactory;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessage;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingLogicalMessageContext;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingPhysicalMessageContext;
use PSB\Core\Pipeline\Incoming\StageContext\InvokeHandlerContext;
use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Transport\IncomingPhysicalMessage;

/**
 * @mixin IncomingContextFactory
 */
class IncomingContextFactorySpec extends ObjectBehavior
{
    /**
     * @var BusOperations
     */
    private $busOperationsMock;
    /**
     * @var OutgoingOptionsFactory
     */
    private $outgoingOptionsFactoryMock;

    function let(
        BusOperations $busOperations,
        OutgoingOptionsFactory $outgoingOptionsFactory
    ) {
        $this->busOperationsMock = $busOperations;
        $this->outgoingOptionsFactoryMock = $outgoingOptionsFactory;
        $this->beConstructedWith($busOperations, $outgoingOptionsFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Incoming\IncomingContextFactory');
    }

    function it_creates_a_physical_message_context(
        TransportReceiveContext $parentContext,
        IncomingPhysicalMessage $message,
        EndpointControlToken $endpointControlToken
    ) {
        $parentContext->getMessage()->willReturn($message);
        $parentContext->getEndpointControlToken()->willReturn($endpointControlToken);
        $message->getMessageId()->willReturn('irrelevant');
        $message->getHeaders()->willReturn(['irrele' => 'vant']);

        $this->createPhysicalMessageContext($parentContext)->shouldBeLike(
            new IncomingPhysicalMessageContext(
                $message->getWrappedObject(),
                'irrelevant',
                ['irrele' => 'vant'],
                new PendingTransportOperations(),
                $this->busOperationsMock->getWrappedObject(),
                $this->outgoingOptionsFactoryMock->getWrappedObject(),
                $endpointControlToken->getWrappedObject(),
                $parentContext->getWrappedObject()
            )
        );
    }

    function it_creates_a_logical_message_context(
        IncomingLogicalMessage $logicalMessage,
        IncomingPhysicalMessageContext $parentContext,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $pendingTransportOperations,
        EndpointControlToken $endpointControlToken
    ) {
        $parentContext->getMessageId()->willReturn('irrelevant');
        $parentContext->getHeaders()->willReturn(['irrele' => 'vant']);
        $parentContext->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $parentContext->getPendingTransportOperations()->willReturn($pendingTransportOperations);
        $parentContext->getEndpointControlToken()->willReturn($endpointControlToken);

        $this->createLogicalMessageContext($logicalMessage, $parentContext)->shouldBeLike(
            new IncomingLogicalMessageContext(
                $logicalMessage->getWrappedObject(),
                'irrelevant',
                ['irrele' => 'vant'],
                $incomingPhysicalMessage->getWrappedObject(),
                $pendingTransportOperations->getWrappedObject(),
                $this->busOperationsMock->getWrappedObject(),
                $this->outgoingOptionsFactoryMock->getWrappedObject(),
                $endpointControlToken->getWrappedObject(),
                $parentContext->getWrappedObject()
            )
        );
    }

    function it_creates_an_invoke_handlers_context(
        MessageHandlerInterface $messageHandler,
        IncomingLogicalMessageContext $parentContext,
        IncomingLogicalMessage $incomingLogicalMessage,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $pendingTransportOperations,
        EndpointControlToken $endpointControlToken
    ) {
        $messageBeingHandled = new \stdClass();
        $parentContext->getMessageId()->willReturn('irrelevant');
        $parentContext->getHeaders()->willReturn(['irrele' => 'vant']);
        $parentContext->getMessage()->willReturn($incomingLogicalMessage);
        $incomingLogicalMessage->getMessageInstance()->willReturn($messageBeingHandled);
        $parentContext->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $parentContext->getPendingTransportOperations()->willReturn($pendingTransportOperations);
        $parentContext->getEndpointControlToken()->willReturn($endpointControlToken);

        $this->createInvokeHandlerContext($messageHandler, $parentContext)->shouldBeLike(
            new InvokeHandlerContext(
                $messageHandler->getWrappedObject(),
                $messageBeingHandled,
                'irrelevant',
                ['irrele' => 'vant'],
                $incomingPhysicalMessage->getWrappedObject(),
                $pendingTransportOperations->getWrappedObject(),
                $this->busOperationsMock->getWrappedObject(),
                $this->outgoingOptionsFactoryMock->getWrappedObject(),
                $endpointControlToken->getWrappedObject(),
                $parentContext->getWrappedObject()
            )
        );
    }
}
