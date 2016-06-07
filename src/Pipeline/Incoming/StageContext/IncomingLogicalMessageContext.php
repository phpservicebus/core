<?php
namespace PSB\Core\Pipeline\Incoming\StageContext;


use PSB\Core\EndpointControlToken;
use PSB\Core\OutgoingOptionsFactory;
use PSB\Core\Pipeline\BusOperations;
use PSB\Core\Pipeline\Incoming\IncomingContext;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessage;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Transport\IncomingPhysicalMessage;

class IncomingLogicalMessageContext extends IncomingContext
{
    /**
     * @var IncomingLogicalMessage
     */
    private $logicalMessage;

    /**
     * @var boolean
     */
    private $isMessageHandled = false;

    /**
     * @param IncomingLogicalMessage         $logicalMessage
     * @param string                         $messageId
     * @param array                          $headers
     * @param IncomingPhysicalMessage        $incomingPhysicalMessage
     * @param PendingTransportOperations     $pendingTransportOperations
     * @param BusOperations                  $busOperations
     * @param OutgoingOptionsFactory         $outgoingOptionsFactory
     * @param EndpointControlToken           $endpointControlToken
     * @param IncomingPhysicalMessageContext $parentContext
     */
    public function __construct(
        IncomingLogicalMessage $logicalMessage,
        $messageId,
        array $headers,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $pendingTransportOperations,
        BusOperations $busOperations,
        OutgoingOptionsFactory $outgoingOptionsFactory,
        EndpointControlToken $endpointControlToken,
        IncomingPhysicalMessageContext $parentContext
    ) {
        parent::__construct(
            $messageId,
            $headers,
            $incomingPhysicalMessage,
            $pendingTransportOperations,
            $busOperations,
            $outgoingOptionsFactory,
            $endpointControlToken,
            $parentContext
        );

        $this->logicalMessage = $logicalMessage;
    }

    /**
     * @return bool
     */
    public function isMessageHandled()
    {
        return $this->isMessageHandled;
    }

    public function markMessageAsHandled()
    {
        $this->isMessageHandled = true;
    }

    /**
     * @return IncomingLogicalMessage
     */
    public function getMessage()
    {
        return $this->logicalMessage;
    }
}
