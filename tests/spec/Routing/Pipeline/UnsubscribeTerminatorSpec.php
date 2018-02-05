<?php

namespace spec\PSB\Core\Routing\Pipeline;

use PhpSpec\ObjectBehavior;

use PSB\Core\Pipeline\Outgoing\StageContext\UnsubscribeContext;
use PSB\Core\Routing\Pipeline\UnsubscribeTerminator;
use PSB\Core\Transport\SubscriptionManagerInterface;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin UnsubscribeTerminator
 */
class UnsubscribeTerminatorSpec extends ObjectBehavior
{
    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManagerMock;

    function let(SubscriptionManagerInterface $subscriptionManager)
    {
        $this->subscriptionManagerMock = $subscriptionManager;
        $this->beConstructedWith($subscriptionManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Routing\Pipeline\UnsubscribeTerminator');
    }

    function it_subscribes_to_the_context_event(
        UnsubscribeContext $context,
        SimpleCallable $next
    ) {
        $irrelevantEvent = 'event';
        $context->getEventFqcn()->willReturn($irrelevantEvent);
        $this->subscriptionManagerMock->unsubscribe($irrelevantEvent)->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(UnsubscribeContext::class);
    }

    function it_reports_with_the_correct_next_stage_context_class()
    {
        self::getNextStageContextClass()->shouldReturn('');
    }
}
