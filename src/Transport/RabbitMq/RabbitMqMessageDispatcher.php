<?php
namespace PSB\Core\Transport\RabbitMq;


use PSB\Core\Exception\InvalidArgumentException;
use PSB\Core\Routing\MulticastAddressTag;
use PSB\Core\Routing\UnicastAddressTag;
use PSB\Core\Transport\MessageDispatcherInterface;
use PSB\Core\Transport\OutgoingPhysicalMessage;
use PSB\Core\Transport\TransportOperations;

class RabbitMqMessageDispatcher implements MessageDispatcherInterface
{
    /**
     * @var RoutingTopology
     */
    private $routingTopology;

    /**
     * @var BrokerModel
     */
    private $brokerModel;

    /**
     * @var MessageConverter
     */
    private $messageConverter;

    /**
     * @param RoutingTopology $routingTopology
     * @param BrokerModel $brokerModel
     * @param MessageConverter $messageConverter
     */
    public function __construct(
        RoutingTopology $routingTopology,
        BrokerModel $brokerModel,
        MessageConverter $messageConverter
    ) {
        $this->routingTopology = $routingTopology;
        $this->brokerModel = $brokerModel;
        $this->messageConverter = $messageConverter;
    }

    /**
     * @param TransportOperations $transportOperations
     */
    public function dispatch(TransportOperations $transportOperations)
    {
        foreach ($transportOperations->getTransportOperations() as $transportOperation) {
            $addressTag = $transportOperation->getAddressTag();
            $message = $transportOperation->getMessage();
            if ($addressTag instanceof UnicastAddressTag) {
                $this->sendMessage($message, $addressTag);
            } elseif ($addressTag instanceof MulticastAddressTag) {
                $this->publishMessage($message, $addressTag);
            } else {
                $tagType = get_class($addressTag);
                throw new InvalidArgumentException(
                    "Transport operations contain an unsupported type of '$tagType'. Supported types are 'PSB\Core\\Routing\\UnicastAddressTag' and 'PSB\Core\\Routing\\MulticastAddressTag'."
                );
            }
        }
    }

    /**
     * @param OutgoingPhysicalMessage $message
     * @param UnicastAddressTag       $addressTag
     */
    private function sendMessage(OutgoingPhysicalMessage $message, UnicastAddressTag $addressTag)
    {
        $attributes = $this->messageConverter->composeRabbitMqAttributes($message);
        $this->routingTopology->send(
            $this->brokerModel,
            $addressTag->getDestination(),
            $message->getBody(),
            $attributes
        );
    }

    /**
     * @param OutgoingPhysicalMessage $message
     * @param MulticastAddressTag     $addressTag
     */
    private function publishMessage(OutgoingPhysicalMessage $message, MulticastAddressTag $addressTag)
    {
        $attributes = $this->messageConverter->composeRabbitMqAttributes($message);
        $this->routingTopology->publish(
            $this->brokerModel,
            $addressTag->getMessageType(),
            $message->getBody(),
            $attributes
        );
    }
}
