<?php

namespace spec\PSB\Core\Routing\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\MessageIntentEnum;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingSendContext;
use PSB\Core\Routing\Pipeline\UnicastSendRoutingConnector;
use PSB\Core\Routing\UnicastAddressTag;
use PSB\Core\Routing\UnicastRouterInterface;
use PSB\Core\SendOptions;
use specsupport\PSB\Core\ParametrizedCallable;

/**
 * @mixin UnicastSendRoutingConnector
 */
class UnicastSendRoutingConnectorSpec extends ObjectBehavior
{
    /**
     * @var UnicastRouterInterface
     */
    private $unicastRouterMock;

    /**
     * @var OutgoingContextFactory
     */
    private $contextFactoryMock;

    function let(
        UnicastRouterInterface $unicastRouter,
        OutgoingContextFactory $contextFactory
    ) {
        $this->unicastRouterMock = $unicastRouter;
        $this->contextFactoryMock = $contextFactory;
        $this->beConstructedWith($unicastRouter, $contextFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Routing\Pipeline\UnicastSendRoutingConnector');
    }

    function it_throws_if_message_destination_cannot_be_determined(
        OutgoingSendContext $context,
        ParametrizedCallable $next
    ) {
        $context->getSendOptions()->willReturn(new SendOptions());
        $context->getMessageClass()->willReturn('');

        $this->unicastRouterMock->route(Argument::any(), '')->willReturn([]);

        $this->shouldThrow('PSB\Core\Exception\RoutingException')->duringInvoke($context, $next);
    }

    function it_sets_the_message_intent_header(OutgoingSendContext $context, ParametrizedCallable $next)
    {
        $context->getSendOptions()->willReturn(new SendOptions());
        $context->getMessageClass()->willReturn('');

        $this->unicastRouterMock->route(Argument::any(), '')->willReturn([new UnicastAddressTag('')]);

        $context->setHeader(HeaderTypeEnum::MESSAGE_INTENT, MessageIntentEnum::SEND)->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_calls_next_using_a_new_context_if_routing_succeeded(
        OutgoingSendContext $context,
        ParametrizedCallable $next,
        OutgoingLogicalMessageContext $logicalMessageContext
    ) {
        $context->getSendOptions()->willReturn(new SendOptions());
        $context->getMessageClass()->willReturn('');
        $context->setHeader(Argument::any(), Argument::any())->willReturn();

        $addressTags = [new UnicastAddressTag('')];
        $this->unicastRouterMock->route(Argument::any(), '')->willReturn($addressTags);
        $this->contextFactoryMock->createLogicalMessageContextFromSendContext($addressTags, $context)->willReturn(
            $logicalMessageContext
        );

        $next->__invoke($logicalMessageContext)->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(OutgoingSendContext::class);
    }

    function it_reports_with_the_correct_next_stage_context_class()
    {
        self::getNextStageContextClass()->shouldReturn(OutgoingLogicalMessageContext::class);
    }
}
