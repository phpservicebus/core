<?php

namespace spec\PSB\Core\Routing\Pipeline;

use PhpSpec\ObjectBehavior;

use PSB\Core\HeaderTypeEnum;
use PSB\Core\MessageIntentEnum;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPhysicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPublishContext;
use PSB\Core\Routing\Pipeline\MulticastPublishRoutingConnector;
use specsupport\PSB\Core\ParametrizedCallable;

/**
 * @mixin MulticastPublishRoutingConnector
 */
class MulticastPublishRoutingConnectorSpec extends ObjectBehavior
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
        $this->shouldHaveType('PSB\Core\Routing\Pipeline\MulticastPublishRoutingConnector');
    }

    function it_sets_the_message_intent_header(OutgoingPublishContext $context, ParametrizedCallable $next)
    {
        $context->setHeader(HeaderTypeEnum::MESSAGE_INTENT, MessageIntentEnum::PUBLISH)->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_calls_next_using_a_new_context(
        OutgoingPublishContext $context,
        ParametrizedCallable $next,
        OutgoingPhysicalMessageContext $outgoingMessageContext
    ) {
        $this->contextFactoryMock->createLogicalMessageContextFromPublishContext($context)->willReturn(
            $outgoingMessageContext
        );

        $next->__invoke($outgoingMessageContext)->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(OutgoingPublishContext::class);
    }

    function it_reports_with_the_correct_next_stage_context_class()
    {
        self::getNextStageContextClass()->shouldReturn(OutgoingLogicalMessageContext::class);
    }
}
