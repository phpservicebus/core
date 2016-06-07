<?php
namespace PSB\Core\Pipeline\Incoming\StageContext;


use PSB\Core\EndpointControlToken;
use PSB\Core\OutgoingOptionsFactory;
use PSB\Core\Pipeline\BusOperations;
use PSB\Core\Pipeline\Incoming\IncomingContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\Transport\IncomingPhysicalMessage;

class IncomingPhysicalMessageContext extends IncomingContext
{
    /**
     * @param IncomingPhysicalMessage    $physicalMessage
     * @param string                     $messageId
     * @param array                      $headers
     * @param PendingTransportOperations $pendingTransportOperations
     * @param BusOperations              $busOperations
     * @param OutgoingOptionsFactory     $outgoingOptionsFactory
     * @param EndpointControlToken       $endpointControlToken
     * @param PipelineStageContext       $parentContext
     */
    public function __construct(
        IncomingPhysicalMessage $physicalMessage,
        $messageId,
        array $headers,
        PendingTransportOperations $pendingTransportOperations,
        BusOperations $busOperations,
        OutgoingOptionsFactory $outgoingOptionsFactory,
        EndpointControlToken $endpointControlToken,
        PipelineStageContext $parentContext
    ) {
        parent::__construct(
            $messageId,
            $headers,
            $physicalMessage,
            $pendingTransportOperations,
            $busOperations,
            $outgoingOptionsFactory,
            $endpointControlToken,
            $parentContext
        );
    }

    /**
     * Returns the physical message being processed
     *
     * @return IncomingPhysicalMessage
     */
    public function getMessage()
    {
        return $this->incomingPhysicalMessage;
    }
}
