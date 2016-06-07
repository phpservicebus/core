<?php

namespace spec\PSB\Core\Serialization\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\Pipeline\Incoming\IncomingContextFactory;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessage;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactory;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingLogicalMessageContext;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingPhysicalMessageContext;
use PSB\Core\Serialization\MessageDeserializerResolver;
use PSB\Core\Serialization\MessageSerializerInterface;
use PSB\Core\Serialization\Pipeline\DeserializeLogicalMessageConnector;
use PSB\Core\Transport\IncomingPhysicalMessage;
use specsupport\PSB\Core\ParametrizedCallable;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin DeserializeLogicalMessageConnector
 */
class DeserializeLogicalMessageConnectorSpec extends ObjectBehavior
{
    /**
     * @var MessageDeserializerResolver
     */
    private $deserializerResolverMock;

    /**
     * @var IncomingLogicalMessageFactory
     */
    private $logicalMessageFactoryMock;

    /**
     * @var IncomingContextFactory
     */
    private $incomingContextFactoryMock;

    function let(
        MessageDeserializerResolver $deserializerResolver,
        IncomingLogicalMessageFactory $logicalMessageFactory,
        IncomingContextFactory $incomingContextFactory
    ) {
        $this->deserializerResolverMock = $deserializerResolver;
        $this->logicalMessageFactoryMock = $logicalMessageFactory;
        $this->incomingContextFactoryMock = $incomingContextFactory;

        $this->beConstructedWith($deserializerResolver, $logicalMessageFactory, $incomingContextFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Serialization\Pipeline\DeserializeLogicalMessageConnector');
    }

    function it_extracts_the_message_and_calls_next_for_it(
        IncomingPhysicalMessageContext $context,
        ParametrizedCallable $next,
        IncomingPhysicalMessage $physicalMessage,
        MessageSerializerInterface $messageSerializer,
        IncomingLogicalMessage $logicalMessage,
        IncomingLogicalMessageContext $incomingLogicalMessageContext
    ) {
        $context->getMessage()->willReturn($physicalMessage);
        $context->getMessageId()->willReturn('id');
        $context->getHeaders()->willReturn([]);
        $physicalMessage->getHeaders()->willReturn([HeaderTypeEnum::ENCLOSED_CLASS => 'SomeClass']);
        $physicalMessage->getBody()->willReturn('body');
        $this->deserializerResolverMock->resolve(Argument::any())->willReturn($messageSerializer);
        $messageSerializer->deserialize('body', 'SomeClass')->willReturn((object)[]);
        $this->logicalMessageFactoryMock->createFromObject((object)[])->willReturn($logicalMessage);
        $this->incomingContextFactoryMock->createLogicalMessageContext($logicalMessage, $context)->willReturn(
            $incomingLogicalMessageContext
        );

        $next->__invoke($incomingLogicalMessageContext)->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_throws_if_message_cannot_be_deserialized(
        IncomingPhysicalMessageContext $context,
        SimpleCallable $next,
        IncomingPhysicalMessage $physicalMessage,
        MessageSerializerInterface $messageSerializer
    ) {
        $context->getMessage()->willReturn($physicalMessage);
        $physicalMessage->getHeaders()->willReturn([HeaderTypeEnum::ENCLOSED_CLASS => 'SomeClass']);
        $physicalMessage->getBody()->willReturn('body');
        $physicalMessage->getMessageId()->willReturn('id');
        $this->deserializerResolverMock->resolve(Argument::any())->willReturn($messageSerializer);
        $messageSerializer->deserialize(Argument::any(), Argument::any())->willThrow(new \Exception());

        $this->shouldThrow('PSB\Core\Exception\MessageDeserializationException')->duringInvoke($context, $next);
    }

    function it_throws_if_physical_body_is_empty(
        IncomingPhysicalMessageContext $context,
        SimpleCallable $next,
        IncomingPhysicalMessage $physicalMessage,
        MessageSerializerInterface $messageSerializer
    ) {
        $context->getMessage()->willReturn($physicalMessage);
        $physicalMessage->getMessageId()->willReturn('someid');
        $physicalMessage->getBody()->willReturn('');
        $this->deserializerResolverMock->resolve(Argument::any())->willReturn($messageSerializer);

        $next->__invoke()->shouldNotBeCalled();

        $this->shouldThrow('PSB\Core\Exception\MessageDeserializationException')->duringInvoke($context, $next);
    }

    function it_throws_if_no_message_class_found_in_headers(
        IncomingPhysicalMessageContext $context,
        SimpleCallable $next,
        IncomingPhysicalMessage $physicalMessage,
        MessageSerializerInterface $messageSerializer
    ) {
        $context->getMessage()->willReturn($physicalMessage);
        $physicalMessage->getMessageId()->willReturn('someid');
        $physicalMessage->getHeaders()->willReturn([]);
        $physicalMessage->getBody()->willReturn('body');
        $this->deserializerResolverMock->resolve(Argument::any())->willReturn($messageSerializer);

        $next->__invoke()->shouldNotBeCalled();

        $this->shouldThrow('PSB\Core\Exception\MessageDeserializationException')->duringInvoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(IncomingPhysicalMessageContext::class);
    }

    function it_reports_with_the_correct_next_stage_context_class()
    {
        self::getNextStageContextClass()->shouldReturn(IncomingLogicalMessageContext::class);
    }
}
