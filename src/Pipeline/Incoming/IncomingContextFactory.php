<?php
namespace PSB\Core\Pipeline\Incoming;


use PSB\Core\MessageHandlerInterface;
use PSB\Core\OutgoingOptionsFactory;
use PSB\Core\Pipeline\BusOperations;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingLogicalMessageContext;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingPhysicalMessageContext;
use PSB\Core\Pipeline\Incoming\StageContext\InvokeHandlerContext;
use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Pipeline\PendingTransportOperations;

class IncomingContextFactory
{
    /**
     * @var BusOperations
     */
    private $busOperations;

    /**
     * @var OutgoingOptionsFactory
     */
    private $outgoingOptionsFactory;

    /**
     * @param BusOperations          $busOperations
     * @param OutgoingOptionsFactory $outgoingOptionsFactory
     */
    public function __construct(
        BusOperations $busOperations,
        OutgoingOptionsFactory $outgoingOptionsFactory
    ) {
        $this->busOperations = $busOperations;
        $this->outgoingOptionsFactory = $outgoingOptionsFactory;
    }

    /**
     * @param TransportReceiveContext $parentContext
     *
     * @return IncomingPhysicalMessageContext
     */
    public function createPhysicalMessageContext(TransportReceiveContext $parentContext)
    {
        $physicalMessage = $parentContext->getMessage();
        return new IncomingPhysicalMessageContext(
            $physicalMessage,
            $physicalMessage->getMessageId(),
            $physicalMessage->getHeaders(),
            new PendingTransportOperations(),
            $this->busOperations,
            $this->outgoingOptionsFactory,
            $parentContext->getEndpointControlToken(),
            $parentContext
        );
    }

    /**
     * @param IncomingLogicalMessage         $logicalMessage
     * @param IncomingPhysicalMessageContext $parentContext
     *
     * @return IncomingLogicalMessageContext
     */
    public function createLogicalMessageContext(
        IncomingLogicalMessage $logicalMessage,
        IncomingPhysicalMessageContext $parentContext
    ) {
        return new IncomingLogicalMessageContext(
            $logicalMessage,
            $parentContext->getMessageId(),
            $parentContext->getHeaders(),
            $parentContext->getIncomingPhysicalMessage(),
            $parentContext->getPendingTransportOperations(),
            $this->busOperations,
            $this->outgoingOptionsFactory,
            $parentContext->getEndpointControlToken(),
            $parentContext
        );
    }

    /**
     * @param MessageHandlerInterface       $messageHandler
     * @param IncomingLogicalMessageContext $parentContext
     *
     * @return InvokeHandlerContext
     */
    public function createInvokeHandlerContext(
        MessageHandlerInterface $messageHandler,
        IncomingLogicalMessageContext $parentContext
    ) {
        return new InvokeHandlerContext(
            $messageHandler,
            $parentContext->getMessage()->getMessageInstance(),
            $parentContext->getMessageId(),
            $parentContext->getHeaders(),
            $parentContext->getIncomingPhysicalMessage(),
            $parentContext->getPendingTransportOperations(),
            $this->busOperations,
            $this->outgoingOptionsFactory,
            $parentContext->getEndpointControlToken(),
            $parentContext
        );
    }
}
