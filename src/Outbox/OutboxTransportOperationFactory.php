<?php
namespace PSB\Core\Outbox;


use PSB\Core\Transport\OutgoingPhysicalMessage;

class OutboxTransportOperationFactory
{
    /**
     * @param OutgoingPhysicalMessage $physicalMessage
     * @param array                   $options
     *
     * @return OutboxTransportOperation
     */
    public function create(OutgoingPhysicalMessage $physicalMessage, array $options)
    {
        return new OutboxTransportOperation(
            $physicalMessage->getMessageId(),
            $options,
            $physicalMessage->getBody(),
            $physicalMessage->getHeaders()
        );
    }
}
