<?php
namespace PSB\Core\Pipeline\Incoming;


use PSB\Core\Exception\InvalidArgumentException;
use PSB\Core\Outbox\OutboxMessage;
use PSB\Core\Outbox\OutboxTransportOperation;
use PSB\Core\Outbox\OutboxTransportOperationFactory;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Routing\AddressTagInterface;
use PSB\Core\Routing\MulticastAddressTag;
use PSB\Core\Routing\UnicastAddressTag;
use PSB\Core\Transport\OutgoingPhysicalMessage;
use PSB\Core\Transport\TransportOperation;

class TransportOperationsConverter
{
    /**
     * @var OutboxTransportOperationFactory
     */
    private $outboxOperationFactory;

    /**
     * @param OutboxTransportOperationFactory $outboxOperationFactory
     */
    public function __construct(OutboxTransportOperationFactory $outboxOperationFactory)
    {
        $this->outboxOperationFactory = $outboxOperationFactory;
    }

    /**
     * @param PendingTransportOperations $transportOperations
     *
     * @return OutboxTransportOperation[]
     */
    public function convertToOutboxOperations(PendingTransportOperations $transportOperations)
    {
        $outboxOperations = [];
        foreach ($transportOperations->getOperations() as $operation) {
            $outboxOperations[] = $this->outboxOperationFactory->create(
                $operation->getMessage(),
                $this->serializeAddressTag($operation->getAddressTag())
            );
        }

        return $outboxOperations;
    }

    /**
     * @param AddressTagInterface $addressTag
     *
     * @return array
     */
    private function serializeAddressTag(AddressTagInterface $addressTag)
    {
        if ($addressTag instanceof UnicastAddressTag) {
            return ['destination' => $addressTag->getDestination()];
        }

        if ($addressTag instanceof MulticastAddressTag) {
            return ['message_type' => $addressTag->getMessageType()];
        }

        throw new InvalidArgumentException("Unknown address tag type :'" . get_class($addressTag) . "'.'");
    }

    /**
     * @param OutboxMessage $outboxMessage
     *
     * @return PendingTransportOperations
     */
    public function convertToPendingTransportOperations(OutboxMessage $outboxMessage)
    {
        $transportOperations = new PendingTransportOperations();
        foreach ($outboxMessage->getTransportOperations() as $outboxOperation) {
            $transportOperations->add(
                new TransportOperation(
                    new OutgoingPhysicalMessage(
                        $outboxOperation->getMessageId(),
                        $outboxOperation->getHeaders(),
                        $outboxOperation->getBody()
                    ),
                    $this->deserializeAddressTag($outboxOperation->getOptions())
                )
            );
        }

        return $transportOperations;
    }

    /**
     * @param array $tagAsArray
     *
     * @return AddressTagInterface
     */
    private function deserializeAddressTag(array $tagAsArray)
    {
        if (isset($tagAsArray['destination'])) {
            return new UnicastAddressTag($tagAsArray['destination']);
        }

        if (isset($tagAsArray['message_type'])) {
            return new MulticastAddressTag($tagAsArray['message_type']);
        }

        throw new InvalidArgumentException("Could not find address tag type to deserialize.");
    }
}
