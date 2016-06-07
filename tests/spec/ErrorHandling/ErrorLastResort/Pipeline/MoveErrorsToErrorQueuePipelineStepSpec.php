<?php

namespace spec\PSB\Core\ErrorHandling\ErrorLastResort\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\ErrorHandling\ErrorLastResort\ExceptionToHeadersConverter;
use PSB\Core\ErrorHandling\ErrorLastResort\Pipeline\MoveErrorsToErrorQueuePipelineStep;
use PSB\Core\Exception\CriticalErrorException;
use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\StageContext\DispatchContext;
use PSB\Core\Pipeline\PipelineInterface;
use PSB\Core\Routing\UnicastAddressTag;
use PSB\Core\Transport\IncomingPhysicalMessage;
use PSB\Core\Transport\OutgoingPhysicalMessage;
use PSB\Core\Transport\TransportOperation;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin MoveErrorsToErrorQueuePipelineStep
 */
class MoveErrorsToErrorQueuePipelineStepSpec extends ObjectBehavior
{
    /**
     * @var ExceptionToHeadersConverter
     */
    private $exceptionConverterMock;

    /**
     * @var OutgoingContextFactory
     */
    private $contextFactoryMock;

    /**
     * @var PipelineInterface
     */
    private $dispatchPipelineMock;

    private $errorQueueAddress = 'irrelevant';
    private $localAddress = 'irrelevant';

    function let(
        PipelineInterface $dispatchPipeline,
        ExceptionToHeadersConverter $exceptionConverter,
        OutgoingContextFactory $contextFactory
    ) {
        $this->dispatchPipelineMock = $dispatchPipeline;
        $this->exceptionConverterMock = $exceptionConverter;
        $this->contextFactoryMock = $contextFactory;
        $this->beConstructedWith(
            $this->errorQueueAddress,
            $this->localAddress,
            $dispatchPipeline,
            $exceptionConverter,
            $contextFactory
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\ErrorHandling\ErrorLastResort\Pipeline\MoveErrorsToErrorQueuePipelineStep');
    }

    function it_just_handles_next_if_no_exceptions_occur(TransportReceiveContext $context, SimpleCallable $next)
    {
        $next->__invoke()->shouldBeCalled();
        $this->invoke($context, $next);
    }

    function it_rethrows_if_critical_error_exception_occurs_during_next_invoke(
        TransportReceiveContext $context,
        SimpleCallable $next
    ) {
        $next->__invoke()->willThrow(new CriticalErrorException('whatever'));

        $this->shouldThrow(CriticalErrorException::class)->duringInvoke($context, $next);
    }

    function it_dispatches_the_message_to_the_error_queue_if_an_exception_occurs(
        TransportReceiveContext $context,
        SimpleCallable $next,
        IncomingPhysicalMessage $incomingMessage,
        DispatchContext $dispatchContext
    ) {
        $messageId = 'irrelevant';
        $messageBody = 'irrelevant';
        $messageHeaders = ['some' => 'header'];
        $exception = new \Exception('whatever');
        $context->getMessage()->willReturn($incomingMessage);
        $incomingMessage->getMessageId()->willReturn($messageId);
        $incomingMessage->getBody()->willReturn($messageBody);
        $incomingMessage->getHeaders()->willReturn($messageHeaders);
        $this->exceptionConverterMock->convert($exception, $this->localAddress)->willReturn(['some other' => 'header']);
        $this->contextFactoryMock->createDispatchContext(
            [new TransportOperation(new OutgoingPhysicalMessage(
                $messageId,
                ['some' => 'header', 'some other' => 'header'],
                $messageBody
            ), new UnicastAddressTag($this->errorQueueAddress))],
            $context
        )->willReturn($dispatchContext);

        $next->__invoke()->willThrow($exception);

        $incomingMessage->revertToOriginalBodyIfNeeded()->shouldBeCalled();
        $this->dispatchPipelineMock->invoke($dispatchContext)->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class(){
        self::getStageContextClass()->shouldReturn(TransportReceiveContext::class);
    }
}
