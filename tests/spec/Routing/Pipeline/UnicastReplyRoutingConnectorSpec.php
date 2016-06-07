<?php

namespace spec\PSB\Core\Routing\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\MessageIntentEnum;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingReplyContext;
use PSB\Core\ReplyOptions;
use PSB\Core\Routing\Pipeline\UnicastReplyRoutingConnector;
use PSB\Core\Transport\IncomingPhysicalMessage;
use spec\PSB\Core\Pipeline\Outgoing\UnicastReplyRoutingConnectorSpec\MockableCallable;
use specsupport\PSB\Core\ParametrizedCallable;

/**
 * @mixin UnicastReplyRoutingConnector
 */
class UnicastReplyRoutingConnectorSpec extends ObjectBehavior
{
    /**
     * @var OutgoingContextFactory
     */
    private $contextFactoryMock;

    function let(OutgoingContextFactory $contextFactory)
    {
        $this->contextFactoryMock = $contextFactory;
        $this->beConstructedWith($contextFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Routing\Pipeline\UnicastReplyRoutingConnector');
    }

    function it_throws_if_not_used_from_a_message_handler(OutgoingReplyContext $context, ParametrizedCallable $next)
    {
        $context->getReplyOptions()->willReturn(new ReplyOptions());
        $context->getIncomingPhysicalMessage()->willReturn(null);

        $this->shouldThrow('PSB\Core\Exception\RoutingException')->duringInvoke($context, $next);
    }

    function it_throws_if_reply_to_address_header_not_set_on_the_incoming_physical_message(
        OutgoingReplyContext $context,
        ParametrizedCallable $next,
        IncomingPhysicalMessage $incomingPhysicalMessage
    ) {
        $context->getReplyOptions()->willReturn(new ReplyOptions());
        $context->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $incomingPhysicalMessage->getHeaders()->willReturn([HeaderTypeEnum::ENCLOSED_CLASS => 'SomeClass']);
        $incomingPhysicalMessage->getReplyToAddress()->willReturn('');

        $this->shouldThrow('PSB\Core\Exception\RoutingException')->duringInvoke($context, $next);
    }

    function it_routes_to_explicit_destination_if_overriden_by_reply_options(
        OutgoingReplyContext $context,
        ParametrizedCallable $next,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        OutgoingLogicalMessageContext $logicalMessageContext
    ) {
        $replyOptions = new ReplyOptions();
        $replyOptions->overrideReplyToAddressOfIncomingMessage('overrideaddress');
        $context->getReplyOptions()->willReturn($replyOptions);
        $context->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $incomingPhysicalMessage->getReplyToAddress()->willReturn('originaladdress');

        $context->setHeader(Argument::any(), Argument::any())->willReturn();

        $this->contextFactoryMock->createLogicalMessageContextFromReplyContext('overrideaddress', $context)->willReturn(
            $logicalMessageContext
        );

        $this->invoke($context, $next);
    }

    function it_routes_to_incoming_physical_message_reply_to_address_if_present(
        OutgoingReplyContext $context,
        ParametrizedCallable $next,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        OutgoingLogicalMessageContext $logicalMessageContext
    ) {
        $context->getReplyOptions()->willReturn(new ReplyOptions());
        $context->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $incomingPhysicalMessage->getReplyToAddress()->willReturn('originaladdress');

        $context->setHeader(Argument::any(), Argument::any())->willReturn();

        $this->contextFactoryMock->createLogicalMessageContextFromReplyContext('originaladdress', $context)->willReturn(
            $logicalMessageContext
        );

        $this->invoke($context, $next);
    }

    function it_sets_the_message_intent_header(
        OutgoingReplyContext $context,
        ParametrizedCallable $next,
        IncomingPhysicalMessage $incomingPhysicalMessage
    ) {
        $context->getReplyOptions()->willReturn(new ReplyOptions());
        $context->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $incomingPhysicalMessage->getReplyToAddress()->willReturn('someaddress');

        $context->setHeader(HeaderTypeEnum::MESSAGE_INTENT, MessageIntentEnum::REPLY)->willReturn();

        $this->invoke($context, $next);
    }

    function it_calls_next_using_a_new_context_if_routing_succeeded(
        OutgoingReplyContext $context,
        ParametrizedCallable $next,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        OutgoingLogicalMessageContext $logicalMessageContext
    ) {
        $context->getReplyOptions()->willReturn(new ReplyOptions());
        $context->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $incomingPhysicalMessage->getReplyToAddress()->willReturn('someaddress');

        $context->setHeader(Argument::any(), Argument::any())->willReturn();

        $this->contextFactoryMock->createLogicalMessageContextFromReplyContext(Argument::any(), $context)->willReturn(
            $logicalMessageContext
        );

        $next->__invoke($logicalMessageContext)->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(OutgoingReplyContext::class);
    }

    function it_reports_with_the_correct_next_stage_context_class()
    {
        self::getNextStageContextClass()->shouldReturn(OutgoingLogicalMessageContext::class);
    }
}
