<?php
namespace PSB\Core\Pipeline\Outgoing\StageContext;


use PSB\Core\Pipeline\Outgoing\OutgoingContext;
use PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessage;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\Routing\AddressTagInterface;
use PSB\Core\Transport\IncomingPhysicalMessage;

class OutgoingLogicalMessageContext extends OutgoingContext
{
    /**
     * @var OutgoingLogicalMessage
     */
    private $logicalMessage;

    /**
     * @var AddressTagInterface[]
     */
    private $addressTags;

    /**
     * @var bool
     */
    private $isImmediateDispatch;

    /**
     * @var null|IncomingPhysicalMessage
     */
    private $incomingPhysicalMessage;

    /**
     * @var PendingTransportOperations|null
     */
    private $pendingTransportOperations;

    /**
     * @param string                     $messageId
     * @param array                      $headers
     * @param OutgoingLogicalMessage     $logicalMessage
     * @param AddressTagInterface[]      $addressTags
     * @param bool                       $isImmediateDispatch
     * @param IncomingPhysicalMessage    $incomingPhysicalMessage
     * @param PendingTransportOperations $pendingTransportOperations
     * @param PipelineStageContext       $parentContext
     */
    public function __construct(
        $messageId,
        array $headers,
        OutgoingLogicalMessage $logicalMessage,
        array $addressTags,
        $isImmediateDispatch,
        IncomingPhysicalMessage $incomingPhysicalMessage = null,
        PendingTransportOperations $pendingTransportOperations = null,
        PipelineStageContext $parentContext
    ) {
        parent::__construct($messageId, $headers, $parentContext);
        $this->logicalMessage = $logicalMessage;
        $this->addressTags = $addressTags;
        $this->isImmediateDispatch = $isImmediateDispatch;
        $this->incomingPhysicalMessage = $incomingPhysicalMessage;
        $this->pendingTransportOperations = $pendingTransportOperations;
    }

    /**
     * @return OutgoingLogicalMessage
     */
    public function getMessage()
    {
        return $this->logicalMessage;
    }

    /**
     * @return AddressTagInterface[]
     */
    public function getAddressTags()
    {
        return $this->addressTags;
    }

    /**
     * @return boolean
     */
    public function isImmediateDispatchEnabled()
    {
        return $this->isImmediateDispatch;
    }

    /**
     * @return null|IncomingPhysicalMessage
     */
    public function getIncomingPhysicalMessage()
    {
        return $this->incomingPhysicalMessage;
    }

    /**
     * @return PendingTransportOperations|null
     */
    public function getPendingTransportOperations()
    {
        return $this->pendingTransportOperations;
    }
}
