<?php
namespace PSB\Core\Transport\RabbitMq;


use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use AMQPQueue;

/**
 * TODO handle exceptions thrown by AMQP
 * TODO handle return values for any amqp operation (setters, publish, declare, etc.). Any false should be critical.
 */
class BrokerModel
{
    /**
     * @var AMQPConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var AMQPExchange[]
     */
    private $declaredExchangeInstances = [];

    /**
     * @var AMQPQueue[]
     */
    private $declaredQueueInstances = [];

    /**
     * @var AMQPExchange[]
     */
    private $usedExchangeInstances = [];

    /**
     * @var AMQPQueue[]
     */
    private $usedQueueInstances = [];

    /**
     * @param AMQPConnection $connection
     */
    public function __construct(AMQPConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $exchangeName
     * @param string $messageBody
     * @param string $routingKey
     * @param int    $flags
     * @param array  $attributes
     *
     * @return bool
     */
    public function publish(
        $exchangeName,
        $messageBody,
        $routingKey = '',
        $flags = AMQP_NOPARAM,
        array $attributes = []
    ) {
        $exchange = $this->produceExchangeInstance($exchangeName);

        $return = $exchange->publish(
            $messageBody,
            $routingKey,
            $flags,
            $attributes
        );

        return $return;
    }

    /**
     * @param string $exchangeName
     * @param string $exchangeType
     * @param int    $flags
     * @param array  $arguments
     */
    public function declareExchange($exchangeName, $exchangeType, $flags = null, $arguments = [])
    {
        $this->ensureChannel();
        if (!isset($this->declaredExchangeInstances[$exchangeName])) {
            $exchange = new AMQPExchange($this->channel);
            $this->declaredExchangeInstances[$exchangeName] = $exchange;
            if ($flags !== null) {
                $exchange->setFlags($flags);
            }
            $exchange->setName($exchangeName);
            $exchange->setType($exchangeType);
            $exchange->setArguments($arguments);
            $exchange->declareExchange();
        }
    }

    /**
     * @param string $queueName
     * @param int    $flags
     * @param array  $arguments
     */
    public function declareQueue($queueName, $flags = null, $arguments = [])
    {
        $this->ensureChannel();

        if (!isset($this->declaredQueueInstances[$queueName])) {
            $queue = new AMQPQueue($this->channel);
            $this->declaredQueueInstances[$queueName] = $queue;
            if ($flags !== null) {
                $queue->setFlags($flags);
            }
            $queue->setName($queueName);
            $queue->setArguments($arguments);
            $queue->declareQueue();
        }
    }

    /**
     * WARNING: amqp 1.4.0 on x64 is bugged and will randomly hang when attempting to bind exchanges
     *
     * @param string $destinationName
     * @param string $sourceName
     * @param string $routingKey
     */
    public function bindExchange($destinationName, $sourceName, $routingKey = '')
    {
        /**
         * Amqp 1.4.0 will throw when trying to bind exchanges without a routing key even if they are of type fanout.
         * Later versions do not, but upgrading from 1.4.0 would result in a duplication of bindings (there would
         * be one with a routing key and one without), so in order to avoid that we use a routing key for all versions.
         */
        if ($routingKey == '') {
            $routingKey = 'making-sure-there-is-a-routing-key';
        }

        $exchange = $this->produceExchangeInstance($destinationName);
        $exchange->bind($sourceName, $routingKey);
    }

    /**
     * @param string $destinationName
     * @param string $sourceName
     * @param string $routingKey
     *
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function unbindExchange($destinationName, $sourceName, $routingKey = '')
    {
        // WARNING: only amqp 1.6.0 and above supports exchange unbinding
        if (phpversion('amqp') <= '1.4.0') {
            return;
        }

        $exchange = $this->produceExchangeInstance($destinationName);

        $exchange->unbind($sourceName, $routingKey);
    }

    /**
     * @param string $queueName
     * @param string $exchangeName
     * @param null   $routingKey
     * @param array  $arguments
     */
    public function bindQueue($queueName, $exchangeName, $routingKey = null, $arguments = [])
    {
        $queue = $this->produceQueueInstance($queueName);
        $queue->bind($exchangeName, $routingKey, $arguments);
    }

    /**
     * @param string $queueName
     * @param string $exchangeName
     * @param null   $routingKey
     * @param array  $arguments
     */
    public function unbindQueue($queueName, $exchangeName, $routingKey = null, $arguments = [])
    {
        $queue = $this->produceQueueInstance($queueName);
        $queue->unbind($exchangeName, $routingKey, $arguments);
    }

    /**
     * @param string $queueName
     */
    public function purgeQueue($queueName)
    {
        $queue = $this->produceQueueInstance($queueName);
        $queue->purge();
    }

    /**
     * @param string   $queueName
     * @param callable $callback
     */
    public function consume($queueName, callable $callback)
    {
        $queue = $this->produceQueueInstance($queueName);
        $queue->consume($callback);
    }

    /**
     * It returns a cached exchange or a new one if none exists.
     *
     * @param string $exchangeName
     *
     * @return AMQPExchange
     */
    private function produceExchangeInstance($exchangeName)
    {
        if (isset($this->declaredExchangeInstances[$exchangeName])) {
            return $this->declaredExchangeInstances[$exchangeName];
        }

        if (isset($this->usedExchangeInstances[$exchangeName])) {
            return $this->usedExchangeInstances[$exchangeName];
        }

        $this->ensureChannel();
        $exchange = new AMQPExchange($this->channel);
        $exchange->setName($exchangeName);
        $this->usedExchangeInstances[$exchangeName] = $exchange;
        return $exchange;
    }

    /**
     * It returns a cached queue or a new one if none exists.
     *
     * @param string $queueName
     *
     * @return AMQPQueue
     */
    private function produceQueueInstance($queueName)
    {
        if (isset($this->declaredQueueInstances[$queueName])) {
            return $this->declaredQueueInstances[$queueName];
        }

        if (isset($this->usedQueueInstances[$queueName])) {
            return $this->usedQueueInstances[$queueName];
        }

        $this->ensureChannel();
        $queue = new AMQPQueue($this->channel);
        $queue->setName($queueName);
        $this->usedQueueInstances[$queueName] = $queue;
        return $queue;
    }

    private function ensureChannel()
    {
        $this->ensureConnection();
        if (!$this->channel) {
            $this->channel = new AMQPChannel($this->connection);
        }
    }

    private function ensureConnection()
    {
        if (!$this->connection->isConnected()) {
            $this->connection->connect();
        }
    }
}
