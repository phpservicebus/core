<?php
namespace PSB\Core\Transport\RabbitMq\Config;


use PSB\Core\KnownSettingsEnum;
use PSB\Core\Transport\Config\TransportInfrastructure;
use PSB\Core\Transport\Config\TransportReceiveInfrastructure;
use PSB\Core\Transport\Config\TransportSendInfrastructure;
use PSB\Core\Transport\Config\TransportSubscriptionInfrastructure;
use PSB\Core\Transport\RabbitMq\BrokerModel;
use PSB\Core\Transport\RabbitMq\MessageConverter;
use PSB\Core\Transport\RabbitMq\MessageProcessor;
use PSB\Core\Transport\RabbitMq\RabbitMqMessageDispatcher;
use PSB\Core\Transport\RabbitMq\RabbitMqMessagePusher;
use PSB\Core\Transport\RabbitMq\RabbitMqQueueCreator;
use PSB\Core\Transport\RabbitMq\RabbitMqSubscriptionManager;
use PSB\Core\Transport\RabbitMq\RoutingTopology;
use PSB\Core\Util\Settings;

class RabbitMqTransportInfrastructure extends TransportInfrastructure
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var RoutingTopology
     */
    private $routingTopology;

    /**
     * @var BrokerModel
     */
    private $brokerModel;

    /**
     * @var bool
     */
    private $durableMessagesEnabled;

    /**
     * @param Settings        $settings
     * @param RoutingTopology $routingTopology
     * @param BrokerModel     $brokerModel
     * @param bool            $durableMessagesEnabled
     */
    public function __construct(
        Settings $settings,
        RoutingTopology $routingTopology,
        BrokerModel $brokerModel,
        $durableMessagesEnabled
    ) {
        $this->settings = $settings;
        $this->routingTopology = $routingTopology;
        $this->brokerModel = $brokerModel;
        $this->durableMessagesEnabled = $durableMessagesEnabled;
    }

    /**
     * @return TransportSendInfrastructure
     */
    public function configureSendInfrastructure()
    {
        return new TransportSendInfrastructure(
            function () {
                return new RabbitMqMessageDispatcher(
                    $this->routingTopology, $this->brokerModel, new MessageConverter()
                );
            }
        );
    }

    /**
     * @return TransportReceiveInfrastructure
     */
    public function configureReceiveInfrastructure()
    {
        return new TransportReceiveInfrastructure(
            function () {
                return new RabbitMqMessagePusher(
                    $this->brokerModel,
                    new MessageProcessor($this->brokerModel, $this->routingTopology, new MessageConverter())
                );
            },
            function () {
                return new RabbitMqQueueCreator(
                    $this->brokerModel,
                    $this->routingTopology,
                    $this->durableMessagesEnabled
                );
            }
        );
    }

    /**
     * @return TransportSubscriptionInfrastructure
     */
    public function configureSubscriptionInfrastructure()
    {
        return new TransportSubscriptionInfrastructure(
            function () {
                return new RabbitMqSubscriptionManager(
                    $this->brokerModel,
                    $this->routingTopology,
                    $this->getLocalAddress()
                );
            }
        );
    }

    /**
     * @param string $logicalAddress
     *
     * @return string
     */
    public function toTransportAddress($logicalAddress)
    {
        return RoutingTopology::getSafeName($logicalAddress);
    }

    /**
     * @return string
     */
    private function getLocalAddress()
    {
        return $this->settings->get(KnownSettingsEnum::LOCAL_ADDRESS);
    }
}
