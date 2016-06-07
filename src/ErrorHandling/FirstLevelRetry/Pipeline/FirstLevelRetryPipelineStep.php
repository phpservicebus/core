<?php
namespace PSB\Core\ErrorHandling\FirstLevelRetry\Pipeline;


use PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryHeaderTypeEnum;
use PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryPolicy;
use PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryStorage;
use PSB\Core\Exception\CriticalErrorException;
use PSB\Core\Exception\MessageDeserializationException;
use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Pipeline\PipelineStepInterface;

class FirstLevelRetryPipelineStep implements PipelineStepInterface
{
    /**
     * @var FirstLevelRetryStorage
     */
    private $retryStorage;

    /**
     * @var FirstLevelRetryPolicy
     */
    private $retryPolicy;

    /**
     * @param FirstLevelRetryStorage $retryStorage
     * @param FirstLevelRetryPolicy  $retryPolicy
     */
    public function __construct(FirstLevelRetryStorage $retryStorage, FirstLevelRetryPolicy $retryPolicy)
    {
        $this->retryStorage = $retryStorage;
        $this->retryPolicy = $retryPolicy;
    }

    /**
     * @param TransportReceiveContext $context
     * @param callable                $next
     *
     * @throws \Exception
     */
    public function invoke($context, callable $next)
    {
        try {
            $next();
        } catch (CriticalErrorException $e) {
            // no retry for critical errors
            throw $e;
        } catch (MessageDeserializationException $e) {
            // no retry for invalid messages
            throw $e;
        } catch (\Exception $e) {
            $messageId = $context->getMessageId();

            $numberOfRetries = $this->retryStorage->getFailuresForMessage($messageId);

            if ($this->retryPolicy->shouldGiveUp($numberOfRetries)) {
                $this->retryStorage->clearFailuresForMessage($messageId);

                $message = $context->getMessage();
                $message->setHeader(FirstLevelRetryHeaderTypeEnum::RETRIES, $numberOfRetries);

                throw $e;
            }

            $this->retryStorage->incrementFailuresForMessage($context->getMessageId());
            $context->abortReceiveOperation();
        }
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return TransportReceiveContext::class;
    }
}
