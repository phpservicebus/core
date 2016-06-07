<?php
namespace PSB\Core\Transport\RabbitMq\Config;


use PSB\Core\Transport\Config\TransportConfigurator;
use PSB\Core\Transport\RabbitMq\RabbitMqKnownSettingsEnum;

class RabbitMqTransportConfigurator extends TransportConfigurator
{
    /**
     * They are the same as the ones in the AMQPConnection documentation/stubs. They may vary with the
     * amqp library version. The ones below are for 1.6.0.
     *
     * $credentials = array(
     *      'host'  => amqp.host The host to connect too. Note: Max 1024 characters.
     *      'port'  => amqp.port Port on the host.
     *      'vhost' => amqp.vhost The virtual host on the host. Note: Max 128 characters.
     *      'login' => amqp.login The login name to use. Note: Max 128 characters.
     *      'password' => amqp.password Password. Note: Max 128 characters.
     *      'read_timeout'  => Timeout in for income activity. Note: 0 or greater seconds. May be fractional.
     *      'write_timeout' => Timeout in for outcome activity. Note: 0 or greater seconds. May be fractional.
     *      'connect_timeout' => Connection timeout. Note: 0 or greater seconds. May be fractional.
     *
     *      Connection tuning options (see http://www.rabbitmq.com/amqp-0-9-1-reference.html#connection.tune for details):
     *      'channel_max' => Specifies highest channel number that the server permits. 0 means standard extension limit
     *                       (see PHP_AMQP_MAX_CHANNELS constant)
     *      'frame_max'   => The largest frame size that the server proposes for the connection, including frame header
     *                       and end-byte. 0 means standard extension limit (depends on librabbimq default frame size limit)
     *      'heartbeat'   => The delay, in seconds, of the connection heartbeat that the server wants.
     *                       0 means the server does not want a heartbeat. Note, librabbitmq has limited heartbeat support,
     *                       which means heartbeats checked only during blocking calls.
     * )
     *
     * @param array $connectionCredentials
     *
     * @return $this
     */
    public function useConnectionCredentials(array $connectionCredentials)
    {
        $this->settings->set(RabbitMqKnownSettingsEnum::CONNECTION_CREDENTIALS, $connectionCredentials);
        return $this;
    }

    /**
     * Instead of the connection credentials one can use an already set up connection object.
     *
     * @param \AMQPConnection $connection
     *
     * @return $this
     */
    public function useConnection(\AMQPConnection $connection)
    {
        $this->settings->set(RabbitMqKnownSettingsEnum::CONNECTION, $connection);
        return $this;
    }
}
