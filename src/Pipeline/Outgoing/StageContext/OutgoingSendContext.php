<?php
namespace PSB\Core\Pipeline\Outgoing\StageContext;


use PSB\Core\Pipeline\Outgoing\OutgoingContext;
use PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessage;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\SendOptions;
use PSB\Core\Transport\IncomingPhysicalMessage;

class OutgoingSendContext extends OutgoingContext
{
    /**
     * @var OutgoingLogicalMessage
     */
    private $logicalMessage;

    /**
     * @var SendOptions
     */
    private $sendOptions;

    /**
     * @var IncomingPhysicalMessage|null
     */
    private $incomingPhysicalMessage;

    /**
     * @var PendingTransportOperations|null
     */
    private $pendingTransportOperations;

    /**
     * @param OutgoingLogicalMessage       $message
     * @param SendOptions                  $options
     * @param IncomingPhysicalMessage|null $incomingPhysicalMessage
     * @param PendingTransportOperations   $transportOperations
     * @param PipelineStageContext         $parentContext
     */
    public function __construct(
        OutgoingLogicalMessage $message,
        SendOptions $options,
        IncomingPhysicalMessage $incomingPhysicalMessage = null,
        PendingTransportOperations $transportOperations = null,
        PipelineStageContext $parentContext
    ) {
        parent::__construct($options->getMessageId(), $options->getOutgoingHeaders(), $parentContext);

        $this->logicalMessage = $message;
        $this->sendOptions = $options;
        $this->incomingPhysicalMessage = $incomingPhysicalMessage;
        $this->pendingTransportOperations = $transportOperations;
    }

    /**
     * @return OutgoingLogicalMessage
     */
    public function getLogicalMessage()
    {
        return $this->logicalMessage;
    }

    /**
     * @return string
     */
    public function getMessageClass()
    {
        return $this->logicalMessage->getMessageClass();
    }

    /**
     * @return PendingTransportOperations
     */
    public function getPendingTransportOperations()
    {
        return $this->pendingTransportOperations;
    }

    /**
     * @return SendOptions
     */
    public function getSendOptions()
    {
        return $this->sendOptions;
    }

    /**
     * @return IncomingPhysicalMessage|null
     */
    public function getIncomingPhysicalMessage()
    {
        return $this->incomingPhysicalMessage;
    }
}
