<?php
namespace PSB\Core\Pipeline\Outgoing\StageContext;


use PSB\Core\Pipeline\Outgoing\OutgoingContext;
use PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessage;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\PublishOptions;
use PSB\Core\Transport\IncomingPhysicalMessage;

class OutgoingPublishContext extends OutgoingContext
{
    /**
     * @var OutgoingLogicalMessage
     */
    private $logicalMessage;

    /**
     * @var PublishOptions
     */
    private $publishOptions;

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
     * @param PublishOptions               $options
     * @param IncomingPhysicalMessage|null $incomingPhysicalMessage
     * @param PendingTransportOperations   $transportOperations
     * @param PipelineStageContext         $parentContext
     */
    public function __construct(
        OutgoingLogicalMessage $message,
        PublishOptions $options,
        IncomingPhysicalMessage $incomingPhysicalMessage = null,
        PendingTransportOperations $transportOperations = null,
        PipelineStageContext $parentContext
    ) {
        parent::__construct($options->getMessageId(), $options->getOutgoingHeaders(), $parentContext);

        $this->logicalMessage = $message;
        $this->publishOptions = $options;
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
     * @return PublishOptions
     */
    public function getPublishOptions()
    {
        return $this->publishOptions;
    }

    /**
     * @return IncomingPhysicalMessage|null
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
