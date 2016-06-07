<?php
namespace PSB\Core\Pipeline\Outgoing\StageContext;


use PSB\Core\Pipeline\Outgoing\OutgoingContext;
use PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessage;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\ReplyOptions;
use PSB\Core\Transport\IncomingPhysicalMessage;

class OutgoingReplyContext extends OutgoingContext
{
    /**
     * @var OutgoingLogicalMessage
     */
    private $logicalMessage;

    /**
     * @var ReplyOptions
     */
    private $replyOptions;

    /**
     * @var IncomingPhysicalMessage
     */
    private $incomingPhysicalMessage;

    /**
     * @var PendingTransportOperations
     */
    private $pendingTransportOperations;

    /**
     * @param OutgoingLogicalMessage     $message
     * @param ReplyOptions               $options
     * @param IncomingPhysicalMessage    $incomingPhysicalMessage
     * @param PendingTransportOperations $transportOperations
     * @param PipelineStageContext       $parentContext
     */
    public function __construct(
        OutgoingLogicalMessage $message,
        ReplyOptions $options,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $transportOperations,
        PipelineStageContext $parentContext
    ) {
        parent::__construct($options->getMessageId(), $options->getOutgoingHeaders(), $parentContext);

        $this->logicalMessage = $message;
        $this->replyOptions = $options;
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
     * @return ReplyOptions
     */
    public function getReplyOptions()
    {
        return $this->replyOptions;
    }

    /**
     * @return IncomingPhysicalMessage
     */
    public function getIncomingPhysicalMessage()
    {
        return $this->incomingPhysicalMessage;
    }

    /**
     * @return PendingTransportOperations
     */
    public function getPendingTransportOperations()
    {
        return $this->pendingTransportOperations;
    }
}
