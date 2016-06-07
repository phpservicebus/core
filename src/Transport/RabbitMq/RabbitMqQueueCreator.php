<?php
namespace PSB\Core\Transport\RabbitMq;


use PSB\Core\Transport\QueueBindings;
use PSB\Core\Transport\QueueCreatorInterface;

class RabbitMqQueueCreator implements QueueCreatorInterface
{
    /**
     * @var BrokerModel
     */
    private $brokerModel;

    /**
     * @var RoutingTopology
     */
    private $routingTopology;

    /**
     * @var bool
     */
    private $useDurableMessages;

    /**
     * @param BrokerModel     $brokerModel
     * @param RoutingTopology $routingTopology
     * @param bool            $useDurableMessages
     */
    public function __construct(BrokerModel $brokerModel, RoutingTopology $routingTopology, $useDurableMessages)
    {
        $this->brokerModel = $brokerModel;
        $this->routingTopology = $routingTopology;
        $this->useDurableMessages = $useDurableMessages;
    }

    /**
     * Creates message queues for the defined queue bindings.
     *
     * @param QueueBindings $queueBindings
     */
    public function createIfNecessary(QueueBindings $queueBindings)
    {
        $addresses = array_merge($queueBindings->getReceivingAddresses(), $queueBindings->getSendingAddresses());
        foreach ($addresses as $address) {
            $this->routingTopology->setupForEndpointUse($this->brokerModel, $address);
        }
    }
}
