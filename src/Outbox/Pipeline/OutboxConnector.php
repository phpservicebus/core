<?php
namespace PSB\Core\Outbox\Pipeline;


use PSB\Core\Outbox\OutboxMessage;
use PSB\Core\Outbox\OutboxStorageInterface;
use PSB\Core\Pipeline\Incoming\IncomingContextFactory;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingPhysicalMessageContext;
use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Pipeline\Incoming\TransportOperationsConverter;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineInterface;
use PSB\Core\Pipeline\StageConnectorInterface;

class OutboxConnector implements StageConnectorInterface
{
    /**
     * @var PipelineInterface
     */
    private $dispatchPipeline;

    /**
     * @var OutboxStorageInterface
     */
    private $outboxStorage;

    /**
     * @var IncomingContextFactory
     */
    private $incomingContextFactory;

    /**
     * @var OutgoingContextFactory
     */
    private $outgoingContextFactory;

    /**
     * @var TransportOperationsConverter
     */
    private $operationsConverter;

    /**
     * @param PipelineInterface            $dispatchPipeline
     * @param OutboxStorageInterface       $outboxStorage
     * @param IncomingContextFactory       $incomingContextFactory
     * @param OutgoingContextFactory       $outgoingContextFactory
     * @param TransportOperationsConverter $operationsConverter
     */
    public function __construct(
        PipelineInterface $dispatchPipeline,
        OutboxStorageInterface $outboxStorage,
        IncomingContextFactory $incomingContextFactory,
        OutgoingContextFactory $outgoingContextFactory,
        TransportOperationsConverter $operationsConverter
    ) {
        $this->dispatchPipeline = $dispatchPipeline;
        $this->outboxStorage = $outboxStorage;
        $this->incomingContextFactory = $incomingContextFactory;
        $this->operationsConverter = $operationsConverter;
        $this->outgoingContextFactory = $outgoingContextFactory;
    }

    /**
     * @param TransportReceiveContext $context
     * @param callable                $next
     *
     * @throws \Exception
     */
    public function invoke($context, callable $next)
    {
        $messageId = $context->getMessageId();
        $physicalMessageContext = $this->incomingContextFactory->createPhysicalMessageContext($context);

        $deduplicationEntry = $this->outboxStorage->get($messageId);
        $pendingTransportOperations = $physicalMessageContext->getPendingTransportOperations();

        if (!$deduplicationEntry) {
            $this->outboxStorage->beginTransaction();

            try {
                $next($physicalMessageContext);

                $outboxOperations = $this->operationsConverter->convertToOutboxOperations($pendingTransportOperations);

                $outboxMessage = new OutboxMessage($messageId, $outboxOperations);
                $this->outboxStorage->store($outboxMessage);
                $this->outboxStorage->commit();
            } catch (\Exception $e) {
                $this->outboxStorage->rollBack();
                throw $e;
            }
        } else {
            $pendingTransportOperations = $this->operationsConverter->convertToPendingTransportOperations(
                $deduplicationEntry
            );
        }

        $this->dispatchPendingOperations($pendingTransportOperations, $physicalMessageContext);

        $this->outboxStorage->markAsDispatched($messageId);
    }

    /**
     * @param PendingTransportOperations     $pendingTransportOperations
     * @param IncomingPhysicalMessageContext $physicalMessageContext
     */
    private function dispatchPendingOperations(
        PendingTransportOperations $pendingTransportOperations,
        IncomingPhysicalMessageContext $physicalMessageContext
    ) {
        if ($pendingTransportOperations->hasOperations()) {
            $dispatchContext = $this->outgoingContextFactory->createDispatchContext(
                $pendingTransportOperations->getOperations(),
                $physicalMessageContext
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

    /**
     * @return string
     */
    public static function getNextStageContextClass()
    {
        return IncomingPhysicalMessageContext::class;
    }
}
