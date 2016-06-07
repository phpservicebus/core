<?php

namespace spec\PSB\Core\ErrorHandling\FirstLevelRetry\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryHeaderTypeEnum;
use PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryPolicy;
use PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryStorage;
use PSB\Core\ErrorHandling\FirstLevelRetry\Pipeline\FirstLevelRetryPipelineStep;
use PSB\Core\Exception\CriticalErrorException;
use PSB\Core\Exception\MessageDeserializationException;
use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Transport\IncomingPhysicalMessage;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin FirstLevelRetryPipelineStep
 */
class FirstLevelRetryPipelineStepSpec extends ObjectBehavior
{
    /**
     * @var FirstLevelRetryStorage
     */
    private $retryStorageMock;

    /**
     * @var FirstLevelRetryPolicy
     */
    private $retryPolicyMock;

    public function let(FirstLevelRetryStorage $retryStorage, FirstLevelRetryPolicy $retryPolicy)
    {
        $this->retryStorageMock = $retryStorage;
        $this->retryPolicyMock = $retryPolicy;
        $this->beConstructedWith($retryStorage, $retryPolicy);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\ErrorHandling\FirstLevelRetry\Pipeline\FirstLevelRetryPipelineStep');
    }

    function it_just_invokes_next_if_no_exception_occurs(TransportReceiveContext $context, SimpleCallable $next)
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

    function it_rethrows_if_deserialization_exception_occurs_during_next_invoke(
        TransportReceiveContext $context,
        SimpleCallable $next
    ) {
        $next->__invoke()->willThrow(new MessageDeserializationException('whatever'));

        $this->shouldThrow(MessageDeserializationException::class)->duringInvoke($context, $next);
    }

    function it_retries_the_message_by_aborting_the_pipeline_if_any_other_exception_occurs_and_it_should_retry(
        TransportReceiveContext $context,
        SimpleCallable $next
    ) {
        $messageId = 'irrelevant';
        $retryCount = 666;
        $next->__invoke()->willThrow(new \Exception('whatever'));
        $context->getMessageId()->willReturn($messageId);

        $this->retryStorageMock->getFailuresForMessage($messageId)->shouldBeCalled()->willReturn($retryCount);
        $this->retryPolicyMock->shouldGiveUp($retryCount)->shouldBeCalled()->willReturn(false);
        $this->retryStorageMock->incrementFailuresForMessage($messageId)->shouldBeCalled();
        $context->abortReceiveOperation()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_rethrows_if_it_should_not_retry(
        TransportReceiveContext $context,
        SimpleCallable $next,
        IncomingPhysicalMessage $incomingMessage
    ) {
        $messageId = 'irrelevant';
        $retryCount = 666;
        $next->__invoke()->willThrow(new \Exception('whatever'));
        $context->getMessageId()->willReturn($messageId);
        $context->getMessage()->willReturn($incomingMessage);

        $this->retryStorageMock->getFailuresForMessage($messageId)->shouldBeCalled()->willReturn($retryCount);
        $this->retryPolicyMock->shouldGiveUp($retryCount)->shouldBeCalled()->willReturn(true);
        $this->retryStorageMock->clearFailuresForMessage($messageId)->shouldBeCalled();
        $incomingMessage->setHeader(FirstLevelRetryHeaderTypeEnum::RETRIES, $retryCount)->shouldBeCalled();

        $context->abortReceiveOperation()->shouldNotBeCalled();

        $this->shouldThrow(new \Exception('whatever'))->duringInvoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class(){
        self::getStageContextClass()->shouldReturn(TransportReceiveContext::class);
    }
}
