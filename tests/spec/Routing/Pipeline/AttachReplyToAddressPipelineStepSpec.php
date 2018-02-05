<?php

namespace spec\PSB\Core\Routing\Pipeline;

use PhpSpec\ObjectBehavior;

use PSB\Core\HeaderTypeEnum;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Routing\Pipeline\AttachReplyToAddressPipelineStep;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin AttachReplyToAddressPipelineStep
 */
class AttachReplyToAddressPipelineStepSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('address');
    }

    function it_is_initializable()
    {

        $this->shouldHaveType('PSB\Core\Routing\Pipeline\AttachReplyToAddressPipelineStep');
    }

    function it_attaches_the_reply_to_address_header(OutgoingLogicalMessageContext $context, SimpleCallable $next)
    {
        $context->setHeader(HeaderTypeEnum::REPLY_TO_ADDRESS, 'address')->shouldbeCalled();

        $this->invoke($context, $next);
    }

    function it_calls_next_step(OutgoingLogicalMessageContext $context, SimpleCallable $next)
    {
        $next->__invoke()->shouldbeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(OutgoingLogicalMessageContext::class);
    }
}
