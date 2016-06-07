<?php

namespace spec\PSB\Core\Serialization\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessage;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPhysicalMessageContext;
use PSB\Core\Serialization\MessageSerializerInterface;
use PSB\Core\Serialization\Pipeline\SerializeMessageConnector;
use specsupport\PSB\Core\ParametrizedCallable;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin SerializeMessageConnector
 */
class SerializeMessageConnectorSpec extends ObjectBehavior
{
    /**
     * @var MessageSerializerInterface
     */
    private $serializerMock;

    /**
     * @var OutgoingContextFactory
     */
    private $contextFactoryMock;

    function let(MessageSerializerInterface $serializer, OutgoingContextFactory $contextFactory)
    {
        $this->serializerMock = $serializer;
        $this->contextFactoryMock = $contextFactory;

        $this->beConstructedWith($serializer, $contextFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Serialization\Pipeline\SerializeMessageConnector');
    }

    function it_sets_the_content_type_and_the_enclosed_class_headers(
        OutgoingLogicalMessageContext $context,
        SimpleCallable $next
    ) {
        $context->getMessage()->willReturn(new IncomingLogicalMessage(new \stdClass(), 'someclass'));
        $this->serializerMock->getContentType()->willReturn('json');
        $this->serializerMock->serialize(new \stdClass())->willReturn('body');

        $context->setHeader(HeaderTypeEnum::CONTENT_TYPE, 'json')->shouldBeCalled();
        $context->setHeader(HeaderTypeEnum::ENCLOSED_CLASS, 'someclass')->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_serializes_the_message_and_calls_next_with_a_new_context(
        OutgoingLogicalMessageContext $context,
        ParametrizedCallable $next,
        OutgoingPhysicalMessageContext $nextContext
    ) {
        $context->getMessage()->willReturn(new IncomingLogicalMessage(new \stdClass(), 'someclass'));
        $context->setHeader(Argument::any(), Argument::any())->willReturn();
        $context->setHeader(Argument::any(), Argument::any())->willReturn();
        $this->serializerMock->getContentType()->willReturn('json');
        $this->serializerMock->serialize(new \stdClass())->willReturn('body');
        $this->contextFactoryMock->createPhysicalMessageContext('body', $context)->willReturn($nextContext);

        $next->__invoke($nextContext)->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(OutgoingLogicalMessageContext::class);
    }

    function it_reports_with_the_correct_next_stage_context_class()
    {
        self::getNextStageContextClass()->shouldReturn(OutgoingPhysicalMessageContext::class);
    }
}
