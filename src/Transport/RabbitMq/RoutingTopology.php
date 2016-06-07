<?php
namespace PSB\Core\Transport\RabbitMq;


class RoutingTopology
{
    /**
     * @var bool
     */
    private $useDurableMessaging;

    /**
     * @var bool[]
     */
    private $messageTopologyConfigured = [];

    /**
     * @param bool $useDurableMesaging
     */
    public function __construct($useDurableMesaging)
    {
        $this->useDurableMessaging = $useDurableMesaging;
    }

    /**
     * @param BrokerModel $broker
     * @param string      $queueName
     */
    public function setupForEndpointUse(BrokerModel $broker, $queueName)
    {
        $safeQueueName = static::getSafeName($queueName);
        $broker->declareQueue($safeQueueName, $this->useDurableMessaging ? AMQP_DURABLE : null);
        $this->declareExchange($broker, $safeQueueName);
        $broker->bindQueue($safeQueueName, $safeQueueName, '');
    }

    /**
     * @param BrokerModel $broker
     * @param string      $messageFqcn
     * @param string      $subscriberName
     */
    public function setupSubscription(BrokerModel $broker, $messageFqcn, $subscriberName)
    {
        $this->setupClassSubscriptions($broker, $messageFqcn);
        $broker->bindExchange($subscriberName, $this->getEventExchangeName($messageFqcn));
    }

    /**
     * @param BrokerModel $broker
     * @param string      $messageFqcn
     * @param string      $subscriberName
     */
    public function tearDownSubscription(BrokerModel $broker, $messageFqcn, $subscriberName)
    {
        $broker->unbindExchange($subscriberName, $this->getEventExchangeName($messageFqcn));
    }

    /**
     * @param BrokerModel $broker
     * @param string      $messageFqcn
     * @param string      $messageBody
     * @param array       $attributes
     */
    public function publish(
        BrokerModel $broker,
        $messageFqcn,
        $messageBody,
        array $attributes = []
    ) {
        // The semantics of publish implies that one can publish without caring who listens,
        // which means that it should not throw due to lack of exchange(s) and thus we make sure the exchange(s) exists
        $this->setupClassSubscriptions($broker, $messageFqcn);
        $broker->publish(
            $this->getEventExchangeName($messageFqcn),
            $messageBody,
            '',
            AMQP_NOPARAM,
            $this->enhanceDeliveryMode($attributes)
        );
    }

    /**
     * @param BrokerModel $broker
     * @param string      $address
     * @param string      $messageBody
     * @param array       $attributes
     */
    public function send(
        BrokerModel $broker,
        $address,
        $messageBody,
        array $attributes = []
    ) {
        $broker->publish(
            static::getSafeName($address),
            $messageBody,
            '',
            AMQP_NOPARAM,
            $this->enhanceDeliveryMode($attributes)
        );
    }

    /**
     * @param BrokerModel $broker
     * @param string      $queueName
     * @param string      $messageBody
     * @param array       $attributes
     */
    public function sendToQueue(
        BrokerModel $broker,
        $queueName,
        $messageBody,
        array $attributes = []
    ) {
        $broker->publish(
            '',
            $messageBody,
            static::getSafeName($queueName),
            AMQP_NOPARAM,
            $this->enhanceDeliveryMode($attributes)
        );
    }

    /**
     * @param BrokerModel $broker
     * @param string      $messageFqcn
     */
    private function setupClassSubscriptions(BrokerModel $broker, $messageFqcn)
    {
        if ($this->isMessageConfigured($messageFqcn)) {
            return;
        }

        $this->declareExchange($broker, $this->getEventExchangeName($messageFqcn));
        foreach (class_implements($messageFqcn, true) as $fqin) {
            $this->declareExchange($broker, $this->getEventExchangeName($fqin));
            $broker->bindExchange($this->getEventExchangeName($fqin), $this->getEventExchangeName($messageFqcn));
        }

        $this->markMessageAsConfigured($messageFqcn);
    }

    /**
     * @param string $messageFqcn
     */
    private function markMessageAsConfigured($messageFqcn)
    {
        $this->messageTopologyConfigured[$messageFqcn] = null;
    }

    /**
     * @param string $messageFqcn
     *
     * @return bool
     */
    private function isMessageConfigured($messageFqcn)
    {
        return isset($this->messageTopologyConfigured[$messageFqcn]);
    }

    /**
     * Converts a FQCN to a exchange name.
     * Eg. \Some\Namespaced\Class -> Some.Namespaced:Class
     *
     * @param string $messageFqcn
     *
     * @return string
     */
    private function getEventExchangeName($messageFqcn)
    {
        $messageFqcn = trim($messageFqcn, '\\');
        $pos = strrpos($messageFqcn, '\\');
        if ($pos !== false) {
            $messageFqcn = substr_replace($messageFqcn, ':', $pos, 1);
        }
        return str_replace('\\', '.', $messageFqcn);
    }

    /**
     * Only letters, digits, hyphen, underscore, period or colon are allowed for queue and exchange names in RMQ
     *
     * @param string $address
     *
     * @return string
     */
    static public function getSafeName($address)
    {
        return preg_replace("/[^a-zA-Z0-9-_.:]/i", '.', $address);
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    private function enhanceDeliveryMode(array $attributes)
    {
        $attributes['delivery_mode'] = 1;

        if ($this->useDurableMessaging) {
            $attributes['delivery_mode'] = 2;
        }

        return $attributes;
    }

    /**
     * @param BrokerModel $broker
     * @param string      $exchangeName
     */
    private function declareExchange(BrokerModel $broker, $exchangeName)
    {
        $broker->declareExchange($exchangeName, AMQP_EX_TYPE_FANOUT, $this->useDurableMessaging ? AMQP_DURABLE : null);
    }
}
