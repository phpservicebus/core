<?php

namespace spec\PSB\Core\Pipeline\Incoming;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\Pipeline\Incoming\InvokeHandlerTerminator;
use PSB\Core\Pipeline\Incoming\StageContext\InvokeHandlerContext;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin InvokeHandlerTerminator
 */
class InvokeHandlerTerminatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Incoming\InvokeHandlerTerminator');
    }

    function it_terminates_by_not_invoking_next(
        InvokeHandlerContext $context,
        SimpleCallable $next,
        MessageHandlerInterface $handler,
        $message
    ) {
        $context->getMessageHandler()->willReturn($handler);
        $context->getMessageBeingHandled()->willReturn($message);

        $next->__invoke()->shouldNotBeCalled();

        $this->invoke($context, $next);
    }

    function it_handles_the_message_using_the_handler_contained_in_the_context(
        InvokeHandlerContext $context,
        SimpleCallable $next,
        MessageHandlerInterface $handler,
        $message
    ) {
        $context->getMessageHandler()->willReturn($handler);
        $context->getMessageBeingHandled()->willReturn($message);

        $handler->handle($message, $context)->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class(){
        self::getStageContextClass()->shouldReturn(InvokeHandlerContext::class);
    }

    function it_reports_with_empty_for_the_next_stage_context_class(){
        self::getNextStageContextClass()->shouldReturn('');
    }
}
