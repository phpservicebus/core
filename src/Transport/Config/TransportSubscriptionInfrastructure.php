<?php
namespace PSB\Core\Transport\Config;


class TransportSubscriptionInfrastructure
{
    /**
     * @var callable
     */
    private $subscriptionManagerFactory;

    /**
     * @param callable $subscriptionManagerFactory
     */
    public function __construct(callable $subscriptionManagerFactory)
    {
        $this->subscriptionManagerFactory = $subscriptionManagerFactory;
    }

    /**
     * @return callable
     */
    public function getSubscriptionManagerFactory()
    {
        return $this->subscriptionManagerFactory;
    }
}
