<?php

namespace spec\PSB\Core\Correlation\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Correlation\Pipeline\AttachCorrelationIdPipelineStep;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Transport\IncomingPhysicalMessage;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin AttachCorrelationIdPipelineStep
 */
class AttachCorrelationIdPipelineStepSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Correlation\Pipeline\AttachCorrelationIdPipelineStep');
    }

    function it_attaches_the_correlation_id_from_incoming_message_if_set_and_calls_next(
        OutgoingLogicalMessageContext $context,
        SimpleCallable $next,
        IncomingPhysicalMessage $incomingPhysicalMessage
    ) {
        $correlationId = 'irrelevant';
        $context->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $incomingPhysicalMessage->getHeaders()->willReturn([HeaderTypeEnum::CORRELATION_ID => $correlationId]);

        $context->setHeader(HeaderTypeEnum::CORRELATION_ID, $correlationId)->shouldBeCalled();
        $next->__invoke()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_attaches_the_incoming_message_id_as_correlation_id_if_no_correlation_id_found_and_calls_next(
        OutgoingLogicalMessageContext $context,
        SimpleCallable $next,
        IncomingPhysicalMessage $incomingPhysicalMessage
    ) {
        $incomingMessageId = 'irrelevant';
        $context->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $incomingPhysicalMessage->getHeaders()->willReturn([HeaderTypeEnum::MESSAGE_ID => $incomingMessageId]);

        $context->setHeader(HeaderTypeEnum::CORRELATION_ID, $incomingMessageId)->shouldBeCalled();
        $next->__invoke()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_attaches_the_outgoing_message_id_as_correlation_id_if_no_incoming_message_and_calls_next(
        OutgoingLogicalMessageContext $context,
        SimpleCallable $next
    ) {
        $outgoingMessageId = 'irrelevant';
        $context->getIncomingPhysicalMessage()->willReturn(null);
        $context->getMessageId()->willReturn($outgoingMessageId);

        $context->setHeader(HeaderTypeEnum::CORRELATION_ID, $outgoingMessageId)->shouldBeCalled();
        $next->__invoke()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(OutgoingLogicalMessageContext::class);
    }
}
