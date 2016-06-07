<?php
namespace PSB\Core\Transport\RabbitMq;


use PSB\Core\Transport\SubscriptionManagerInterface;

class RabbitMqSubscriptionManager implements SubscriptionManagerInterface
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
     * @var string
     */
    private $localQueue;

    /**
     * @param BrokerModel     $brokerModel
     * @param RoutingTopology $routingTopology
     * @param string          $localQueue
     */
    public function __construct(BrokerModel $brokerModel, RoutingTopology $routingTopology, $localQueue)
    {
        $this->brokerModel = $brokerModel;
        $this->routingTopology = $routingTopology;
        $this->localQueue = $localQueue;
    }

    /**
     * @param string $eventFqcn
     */
    public function subscribe($eventFqcn)
    {
        $this->routingTopology->setupSubscription($this->brokerModel, $eventFqcn, $this->localQueue);
    }

    /**
     * @param string $eventFqcn
     */
    public function unsubscribe($eventFqcn)
    {
        $this->routingTopology->tearDownSubscription($this->brokerModel, $eventFqcn, $this->localQueue);
    }
}
