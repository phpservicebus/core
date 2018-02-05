<?php

namespace spec\PSB\Core\Outbox\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Outbox\OutboxMessage;
use PSB\Core\Outbox\OutboxStorageInterface;
use PSB\Core\Outbox\Pipeline\OutboxConnector;
use PSB\Core\Pipeline\Incoming\IncomingContextFactory;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingPhysicalMessageContext;
use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Pipeline\Incoming\TransportOperationsConverter;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\StageContext\DispatchContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineInterface;
use PSB\Core\Routing\UnicastAddressTag;
use PSB\Core\Transport\OutgoingPhysicalMessage;
use PSB\Core\Transport\TransportOperation;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin OutboxConnector
 */
class OutboxConnectorSpec extends ObjectBehavior
{
    /**
     * @var PipelineInterface
     */
    private $dispatchPipelineMock;

    /**
     * @var OutboxStorageInterface
     */
    private $outboxStorageMock;

    /**
     * @var IncomingContextFactory
     */
    private $incomingContextFactoryMock;

    /**
     * @var OutgoingContextFactory
     */
    private $outgoingContextFactoryMock;

    /**
     * @var TransportOperationsConverter
     */
    private $operationsConverterMock;

    function let(
        PipelineInterface $dispatchPipeline,
        OutboxStorageInterface $outboxStorage,
        IncomingContextFactory $incomingContextFactory,
        OutgoingContextFactory $outgoingContextFactory,
        TransportOperationsConverter $operationsConverter
    ) {
        $this->dispatchPipelineMock = $dispatchPipeline;
        $this->outboxStorageMock = $outboxStorage;
        $this->incomingContextFactoryMock = $incomingContextFactory;
        $this->outgoingContextFactoryMock = $outgoingContextFactory;
        $this->operationsConverterMock = $operationsConverter;

        $this->beConstructedWith(
            $dispatchPipeline,
            $outboxStorage,
            $incomingContextFactory,
            $outgoingContextFactory,
            $operationsConverter
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Outbox\Pipeline\OutboxConnector');
    }

    function it_does_not_invoke_next_when_message_is_duplicate(
        TransportReceiveContext $context,
        SimpleCallable $next,
        OutboxMessage $outboxMessage,
        IncomingPhysicalMessageContext $physicalMessageContext
    ) {
        $this->incomingContextFactoryMock->createPhysicalMessageContext($context)->willReturn($physicalMessageContext);
        $this->outboxStorageMock->get(Argument::any())->willReturn($outboxMessage);
        $this->operationsConverterMock->convertToPendingTransportOperations($outboxMessage)->willReturn(
            new PendingTransportOperations()
        );
        $this->outboxStorageMock->markAsDispatched(Argument::any())->willReturn();

        $next->__invoke()->shouldNotBeCalled();

        $this->invoke($context, $next);
    }

    function it_dispatches_pending_transport_operations_if_message_is_not_duplicate(
        TransportReceiveContext $context,
        SimpleCallable $next,
        IncomingPhysicalMessageContext $physicalMessageContext,
        DispatchContext $dispatchContext
    ) {
        $context->getMessageId()->willReturn('id');
        $this->incomingContextFactoryMock->createPhysicalMessageContext($context)->willReturn($physicalMessageContext);
        $this->outboxStorageMock->get(Argument::any())->willReturn(null);
        $transportOperations = new PendingTransportOperations();
        $outgoingMessage = new OutgoingPhysicalMessage('id', [], '');
        $transportOperations->add(new TransportOperation($outgoingMessage, new UnicastAddressTag('queuename')));
        $physicalMessageContext->getPendingTransportOperations()->willReturn($transportOperations);
        $this->outgoingContextFactoryMock->createDispatchContext(Argument::any(), $physicalMessageContext)->willReturn(
            $dispatchContext
        );
        $this->operationsConverterMock->convertToOutboxOperations($transportOperations)->willReturn([]);

        $this->outboxStorageMock->beginTransaction()->shouldBeCalled();
        $this->outboxStorageMock->store(Argument::type('PSB\Core\Outbox\OutboxMessage'))->shouldBeCalled();
        $this->outboxStorageMock->commit()->shouldBeCalled();
        $this->outboxStorageMock->markAsDispatched(Argument::any())->shouldBeCalled();
        $this->dispatchPipelineMock->invoke($dispatchContext)->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_dispatches_pending_transport_operations_if_message_is_duplicate(
        TransportReceiveContext $context,
        SimpleCallable $next,
        OutboxMessage $outboxMessage,
        IncomingPhysicalMessageContext $physicalMessageContext,
        DispatchContext $dispatchContext
    ) {
        $this->incomingContextFactoryMock->createPhysicalMessageContext($context)->willReturn($physicalMessageContext);
        $this->outboxStorageMock->get(Argument::any())->willReturn($outboxMessage);
        $transportOperations = new PendingTransportOperations();
        $outgoingMessage = new OutgoingPhysicalMessage('id', [], '');
        $transportOperations->add(new TransportOperation($outgoingMessage, new UnicastAddressTag('queuename')));
        $this->operationsConverterMock->convertToPendingTransportOperations($outboxMessage)->willReturn(
            $transportOperations
        );
        $this->outgoingContextFactoryMock->createDispatchContext(Argument::any(), $physicalMessageContext)->willReturn(
            $dispatchContext
        );

        $this->dispatchPipelineMock->invoke($dispatchContext)->shouldBeCalled();
        $this->outboxStorageMock->markAsDispatched(Argument::any())->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_throws_and_rolls_back_if_next_middleware_throws(
        TransportReceiveContext $context,
        SimpleCallable $next,
        IncomingPhysicalMessageContext $physicalMessageContext
    ) {
        $context->getMessageId()->willReturn('id');
        $next->__invoke()->willThrow(new \Exception());
        $this->incomingContextFactoryMock->createPhysicalMessageContext($context)->willReturn($physicalMessageContext);
        $this->outboxStorageMock->get(Argument::any())->willReturn(null);
        $transportOperations = new PendingTransportOperations();
        $outgoingMessage = new OutgoingPhysicalMessage('id', [], '');
        $transportOperations->add(new TransportOperation($outgoingMessage, new UnicastAddressTag('queuename')));
        $physicalMessageContext->getPendingTransportOperations()->willReturn($transportOperations);

        $this->outboxStorageMock->beginTransaction()->shouldBeCalled();
        $this->outboxStorageMock->rollBack()->shouldBeCalled();
        $this->shouldThrow()->duringInvoke($context, $next);
    }

    function it_does_not_dispatch_if_there_are_no_pending_operations(
        TransportReceiveContext $context,
        SimpleCallable $next,
        OutboxMessage $outboxMessage,
        IncomingPhysicalMessageContext $physicalMessageContext,
        DispatchContext $dispatchContext
    ) {
        $this->incomingContextFactoryMock->createPhysicalMessageContext($context)->willReturn($physicalMessageContext);
        $this->outboxStorageMock->get(Argument::any())->willReturn($outboxMessage);
        $transportOperations = new PendingTransportOperations();
        $this->operationsConverterMock->convertToPendingTransportOperations($outboxMessage)->willReturn(
            $transportOperations
        );

        $this->dispatchPipelineMock->invoke($dispatchContext)->shouldNotBeCalled();
        $this->outboxStorageMock->markAsDispatched(Argument::any())->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(TransportReceiveContext::class);
    }

    function it_reports_with_the_correct_next_stage_context_class()
    {
        self::getNextStageContextClass()->shouldReturn(IncomingPhysicalMessageContext::class);
    }
}
