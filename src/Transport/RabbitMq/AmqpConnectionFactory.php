<?php
namespace PSB\Core\Transport\RabbitMq;


use AMQPConnection;
use PSB\Core\Transport\TransportConnectionFactoryInterface;
use PSB\Core\Util\Settings;

class AmqpConnectionFactory implements TransportConnectionFactoryInterface
{
    /**
     * @var array
     */
    private $defaultCredentials = [
        'host' => '127.0.0.1',
        'port' => '5672',
        'vhost' => '/',
        'login' => 'guest',
        'password' => 'guest',
        'heartbeat' => 5
    ];

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return AMQPConnection
     */
    public function createConnection()
    {
        /** @var AMQPConnection $connection */
        $connection = $this->settings->tryGet(RabbitMqKnownSettingsEnum::CONNECTION);

        if (!$connection) {
            $connectionCredentials = $this->settings->tryGet(RabbitMqKnownSettingsEnum::CONNECTION_CREDENTIALS) ?: [];
            $connectionCredentials = array_replace($this->defaultCredentials, $connectionCredentials);
            $connection = new AMQPConnection($connectionCredentials);
        }

        return $connection;
    }
}
