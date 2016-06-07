<?php

namespace spec\PSB\Core\Pipeline\Outgoing;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Pipeline\Outgoing\ImmediateDispatchTerminator;
use PSB\Core\Pipeline\Outgoing\StageContext\DispatchContext;
use PSB\Core\Transport\MessageDispatcherInterface;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin ImmediateDispatchTerminator
 */
class ImmediateDispatchTerminatorSpec extends ObjectBehavior
{
    /**
     * @var MessageDispatcherInterface
     */
    private $messageDispatcherMock;

    function let(MessageDispatcherInterface $messageDispatcher)
    {
        $this->messageDispatcherMock = $messageDispatcher;
        $this->beConstructedWith($messageDispatcher);
    }

    function it_is_initializable(MessageDispatcherInterface $messageDispatcher)
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Outgoing\ImmediateDispatchTerminator');
    }

    function it_dispathes_the_transport_operations_and_does_not_call_next(
        DispatchContext $context,
        SimpleCallable $next
    ) {
        $context->getTransportOperations()->willReturn([]);

        $this->messageDispatcherMock->dispatch(Argument::type('PSB\Core\Transport\TransportOperations'))->shouldBeCalled();
        $next->__invoke()->shouldNotBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(DispatchContext::class);
    }

    function it_reports_with_empty_for_the_next_stage_context_class()
    {
        self::getNextStageContextClass()->shouldReturn('');
    }
}
