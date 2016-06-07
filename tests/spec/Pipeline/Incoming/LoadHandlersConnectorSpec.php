<?php

namespace spec\PSB\Core\Pipeline\Incoming;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\MessageHandlerRegistry;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Incoming\IncomingContextFactory;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessage;
use PSB\Core\Pipeline\Incoming\LoadHandlersConnector;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingLogicalMessageContext;
use PSB\Core\Pipeline\Incoming\StageContext\InvokeHandlerContext;
use spec\PSB\Core\Pipeline\Incoming\LoadHandlersConnectorSpec\AbortingInvokeHandlerContextMockableCallable;
use spec\PSB\Core\Pipeline\Incoming\LoadHandlersConnectorSpec\InvokeHandlerContextMockableCallable;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin LoadHandlersConnector
 */
class LoadHandlersConnectorSpec extends ObjectBehavior
{
    /**
     * @var MessageHandlerRegistry
     */
    private $messageHandlerRegistryMock;

    /**
     * @var IncomingContextFactory
     */
    private $incomingContextFactoryMock;

    function let(MessageHandlerRegistry $messageHandlerRegistry, IncomingContextFactory $incomingContextFactoryMock)
    {
        $this->messageHandlerRegistryMock = $messageHandlerRegistry;
        $this->incomingContextFactoryMock = $incomingContextFactoryMock;
        $this->beConstructedWith($messageHandlerRegistry, $incomingContextFactoryMock);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Incoming\LoadHandlersConnector');
    }

    function it_throws_exception_if_no_message_handlers_are_registered(
        IncomingLogicalMessageContext $context,
        SimpleCallable $next,
        IncomingLogicalMessage $message
    ) {
        $context->getMessage()->willReturn($message);
        $context->isMessageHandled()->willReturn(false);
        $message->getMessageTypes()->willReturn([]);
        $this->messageHandlerRegistryMock->getHandlerIdsFor([])->willReturn([]);

        $this->shouldThrow('PSB\Core\Exception\UnexpectedValueException')->duringInvoke($context, $next);
    }

    function it_reconfirms_being_handled_if_message_is_already_handled(
        IncomingLogicalMessageContext $context,
        SimpleCallable $next,
        IncomingLogicalMessage $message
    ) {
        $context->getMessage()->willReturn($message);
        $context->isMessageHandled()->willReturn(true);
        $message->getMessageTypes()->willReturn([]);
        $this->messageHandlerRegistryMock->getHandlerIdsFor([])->willReturn([]);

        $context->markMessageAsHandled()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_calls_each_of_the_registered_handlers(
        IncomingLogicalMessageContext $context,
        InvokeHandlerContextMockableCallable $next,
        IncomingLogicalMessage $message,
        BuilderInterface $builder,
        MessageHandlerInterface $handler,
        InvokeHandlerContext $invokeHandlerContext
    ) {
        $context->getMessage()->willReturn($message);
        $message->getMessageTypes()->willReturn(['', '']);
        $this->messageHandlerRegistryMock->getHandlerIdsFor(['', ''])->willReturn(['', '']);
        $context->isMessageHandled()->willReturn(false);
        $context->getBuilder()->willReturn($builder);
        $builder->build(Argument::any())->willReturn($handler);
        $this->incomingContextFactoryMock->createInvokeHandlerContext($handler, $context)->willReturn(
            $invokeHandlerContext
        );

        $next->__invoke(Argument::type('PSB\Core\Pipeline\Incoming\StageContext\InvokeHandlerContext'))->shouldBeCalledTimes(
            2
        );

        $context->markMessageAsHandled()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_calls_each_of_the_registered_handlers_until_one_of_them_aborts(
        IncomingLogicalMessageContext $context,
        AbortingInvokeHandlerContextMockableCallable $next,
        IncomingLogicalMessage $message,
        BuilderInterface $builder,
        MessageHandlerInterface $handler,
        InvokeHandlerContext $invokeHandlerContext
    ) {
        $context->getMessage()->willReturn($message);
        $message->getMessageTypes()->willReturn(['', '']);
        $this->messageHandlerRegistryMock->getHandlerIdsFor(['', ''])->willReturn(['', '']);
        $context->isMessageHandled()->willReturn(false);
        $context->getBuilder()->willReturn($builder);
        $builder->build(Argument::any())->willReturn($handler);
        $this->incomingContextFactoryMock->createInvokeHandlerContext($handler, $context)->willReturn(
            $invokeHandlerContext
        );
        $invokeHandlerContext->doNotContinueDispatchingCurrentMessageToHandlers()->willReturn();
        $invokeHandlerContext->isHandlerInvocationAborted()->willReturn(true);

        $next->__invoke(Argument::type('PSB\Core\Pipeline\Incoming\StageContext\InvokeHandlerContext'))->shouldBeCalledTimes(
            1
        );

        $context->markMessageAsHandled()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class(){
        self::getStageContextClass()->shouldReturn(IncomingLogicalMessageContext::class);
    }

    function it_reports_with_the_correct_next_stage_context_class(){
        self::getNextStageContextClass()->shouldReturn(InvokeHandlerContext::class);
    }
}

namespace spec\PSB\Core\Pipeline\Incoming\LoadHandlersConnectorSpec;

use PSB\Core\Pipeline\Incoming\StageContext\InvokeHandlerContext;

class InvokeHandlerContextMockableCallable
{
    public function __invoke(InvokeHandlerContext $context)
    {
        $this->invoke($context);
    }

    public function invoke(InvokeHandlerContext $context)
    {

    }
}

class AbortingInvokeHandlerContextMockableCallable
{
    public function __invoke(InvokeHandlerContext $context)
    {
        $context->doNotContinueDispatchingCurrentMessageToHandlers();
        $this->invoke($context);
    }

    public function invoke(InvokeHandlerContext $context)
    {

    }
}
