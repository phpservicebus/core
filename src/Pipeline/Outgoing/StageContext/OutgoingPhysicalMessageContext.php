<?php
namespace PSB\Core\Pipeline\Outgoing\StageContext;


use PSB\Core\Pipeline\Outgoing\OutgoingContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\Routing\AddressTagInterface;
use PSB\Core\Transport\IncomingPhysicalMessage;
use PSB\Core\Transport\OutgoingPhysicalMessage;

class OutgoingPhysicalMessageContext extends OutgoingContext
{
    /**
     * @var OutgoingPhysicalMessage
     */
    private $physicalMessage;

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
     * @param OutgoingPhysicalMessage    $physicalMessage
     * @param AddressTagInterface[]      $addressTags
     * @param bool                       $isImmediateDispatch
     * @param IncomingPhysicalMessage    $incomingPhysicalMessage
     * @param PendingTransportOperations $pendingTransportOperations
     * @param PipelineStageContext       $parentContext
     */
    public function __construct(
        $messageId,
        array $headers,
        OutgoingPhysicalMessage $physicalMessage,
        array $addressTags,
        $isImmediateDispatch,
        IncomingPhysicalMessage $incomingPhysicalMessage = null,
        PendingTransportOperations $pendingTransportOperations = null,
        PipelineStageContext $parentContext
    ) {
        parent::__construct($messageId, $headers, $parentContext);
        $this->physicalMessage = $physicalMessage;
        $this->addressTags = $addressTags;
        $this->isImmediateDispatch = $isImmediateDispatch;
        $this->incomingPhysicalMessage = $incomingPhysicalMessage;
        $this->pendingTransportOperations = $pendingTransportOperations;
    }

    /**
     * @return OutgoingPhysicalMessage
     */
    public function getMessage()
    {
        return $this->physicalMessage;
    }

    /**
     * @return AddressTagInterface[]
     */
    public function getAddressTags()
    {
        return $this->addressTags;
    }

    /**
     * @return bool
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

    /**
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        $this->physicalMessage->setHeader($name, $value);
    }
}
