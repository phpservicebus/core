<?php

namespace spec\PSB\Core\Pipeline\Outgoing;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\DateTimeConverter;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\OutgoingPhysicalToDispatchConnector;
use PSB\Core\Pipeline\Outgoing\StageContext\DispatchContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPhysicalMessageContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Routing\UnicastAddressTag;
use PSB\Core\Transport\OutgoingPhysicalMessage;
use PSB\Core\Transport\TransportOperation;
use PSB\Core\Util\Clock\ClockInterface;
use specsupport\PSB\Core\ParametrizedCallable;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin OutgoingPhysicalToDispatchConnector
 */
class OutgoingPhysicalToDispatchConnectorSpec extends ObjectBehavior
{
    /**
     * @var OutgoingContextFactory
     */
    private $contextFactoryMock;

    /**
     * @var DateTimeConverter
     */
    private $dateTimeConverterMock;

    /**
     * @var ClockInterface
     */
    private $clockMock;

    function let(
        OutgoingContextFactory $contextFactory,
        DateTimeConverter $dateTimeConverter,
        ClockInterface $clock
    ) {
        $this->contextFactoryMock = $contextFactory;
        $this->dateTimeConverterMock = $dateTimeConverter;
        $this->clockMock = $clock;
        $this->beConstructedWith($contextFactory, $dateTimeConverter, $clock);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Outgoing\OutgoingPhysicalToDispatchConnector');
    }

    function it_adds_trasport_operations_to_pending_and_exits_if_immediate_dispatch_not_requested(
        OutgoingPhysicalMessageContext $context,
        SimpleCallable $next,
        PendingTransportOperations $pendingTransportOperations
    ) {
        $message = new OutgoingPhysicalMessage('id', [], 'body');
        $tags = [new UnicastAddressTag('dest1'), new UnicastAddressTag('dest2')];
        $currentTime = '2016-03-11T03:45:40Z';
        $currentDateTime = new \DateTime($currentTime);

        $context->getMessageId()->willReturn('id');
        $context->getMessage()->willReturn($message);
        $context->getAddressTags()->willReturn($tags);
        $context->getPendingTransportOperations()->willReturn($pendingTransportOperations);
        $this->clockMock->now()->willReturn($currentDateTime);
        $this->dateTimeConverterMock->toWireFormattedString($currentDateTime)->willReturn($currentTime);

        $context->isImmediateDispatchEnabled()->willReturn(false);

        $context->setHeader(HeaderTypeEnum::MESSAGE_ID, 'id')->shouldBeCalled();
        $context->setHeader(HeaderTypeEnum::TIME_SENT, $currentTime)->shouldBeCalled();

        $pendingTransportOperations->addAll(
            [new TransportOperation($message, $tags[0]), new TransportOperation($message, $tags[1])]
        )->shouldBeCalled();

        $next->__invoke()->shouldNotBeCalled();

        $this->invoke($context, $next);
    }

    function it_passes_the_transport_operations_to_the_next_step_if_immediate_dispatch_requested(
        OutgoingPhysicalMessageContext $context,
        ParametrizedCallable $next,
        PendingTransportOperations $pendingTransportOperations,
        DispatchContext $dispatchContext
    ) {
        $message = new OutgoingPhysicalMessage('id', [], 'body');
        $tags = [new UnicastAddressTag('dest1'), new UnicastAddressTag('dest2')];
        $currentTime = '2016-03-11T03:45:40Z';
        $currentDateTime = new \DateTime($currentTime);

        $context->getMessageId()->willReturn('id');
        $context->getMessage()->willReturn($message);
        $context->getAddressTags()->willReturn($tags);
        $context->getPendingTransportOperations()->willReturn($pendingTransportOperations);
        $this->contextFactoryMock->createDispatchContext(
            [new TransportOperation($message, $tags[0]), new TransportOperation($message, $tags[1])],
            $context
        )->willReturn($dispatchContext);
        $this->clockMock->now()->willReturn($currentDateTime);
        $this->dateTimeConverterMock->toWireFormattedString($currentDateTime)->willReturn($currentTime);

        $context->isImmediateDispatchEnabled()->willReturn(true);

        $context->setHeader(HeaderTypeEnum::MESSAGE_ID, 'id')->shouldBeCalled();
        $context->setHeader(HeaderTypeEnum::TIME_SENT, $currentTime)->shouldBeCalled();

        $pendingTransportOperations->addAll(Argument::any())->shouldNotBeCalled();

        $next->__invoke($dispatchContext)->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class(){
        self::getStageContextClass()->shouldReturn(OutgoingPhysicalMessageContext::class);
    }

    function it_reports_with_the_correct_next_stage_context_class(){
        self::getNextStageContextClass()->shouldReturn(DispatchContext::class);
    }
}
