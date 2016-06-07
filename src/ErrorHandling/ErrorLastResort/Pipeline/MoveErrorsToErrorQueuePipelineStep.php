<?php
namespace PSB\Core\ErrorHandling\ErrorLastResort\Pipeline;


use PSB\Core\ErrorHandling\ErrorLastResort\ExceptionToHeadersConverter;
use PSB\Core\Exception\CriticalErrorException;
use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\PipelineInterface;
use PSB\Core\Pipeline\PipelineStepInterface;
use PSB\Core\Routing\UnicastAddressTag;
use PSB\Core\Transport\OutgoingPhysicalMessage;
use PSB\Core\Transport\TransportOperation;

class MoveErrorsToErrorQueuePipelineStep implements PipelineStepInterface
{
    /**
     * @var string
     */
    private $errorQueueAddress;

    /**
     * @var string
     */
    private $localAddress;

    /**
     * @var PipelineInterface
     */
    private $dispatchPipeline;

    /**
     * @var ExceptionToHeadersConverter
     */
    private $exceptionConverter;

    /**
     * @var OutgoingContextFactory
     */
    private $contextFactory;

    /**
     * @param string                      $errorQueueAddress
     * @param string                      $localAddress
     * @param PipelineInterface           $dispatchPipeline
     * @param ExceptionToHeadersConverter $exceptionConverter
     * @param OutgoingContextFactory      $contextFactory
     */
    public function __construct(
        $errorQueueAddress,
        $localAddress,
        PipelineInterface $dispatchPipeline,
        ExceptionToHeadersConverter $exceptionConverter,
        OutgoingContextFactory $contextFactory
    ) {
        $this->errorQueueAddress = $errorQueueAddress;
        $this->localAddress = $localAddress;
        $this->dispatchPipeline = $dispatchPipeline;
        $this->exceptionConverter = $exceptionConverter;
        $this->contextFactory = $contextFactory;
    }

    /**
     * @param TransportReceiveContext $context
     * @param callable                $next
     */
    public function invoke($context, callable $next)
    {
        try {
            $next();
        } catch (CriticalErrorException $e) {
            // all hope is gone
            throw $e;
        } catch (\Exception $e) {
            $incomingMessage = $context->getMessage();

            $incomingMessage->revertToOriginalBodyIfNeeded();

            $exceptionHeaders = $this->exceptionConverter->convert($e, $this->localAddress);
            $newHeaders = array_merge($incomingMessage->getHeaders(), $exceptionHeaders);

            $outgoingMessage = new OutgoingPhysicalMessage(
                $incomingMessage->getMessageId(),
                $newHeaders,
                $incomingMessage->getBody()
            );
            $dispatchContext = $this->contextFactory->createDispatchContext(
                [new TransportOperation($outgoingMessage, new UnicastAddressTag($this->errorQueueAddress))],
                $context
            );
            $this->dispatchPipeline->invoke($dispatchContext);
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
