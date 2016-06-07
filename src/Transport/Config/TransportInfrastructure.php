<?php
namespace PSB\Core\Transport\Config;


abstract class TransportInfrastructure
{
    /**
     * @return TransportSendInfrastructure
     */
    abstract public function configureSendInfrastructure();

    /**
     * @return TransportReceiveInfrastructure
     */
    abstract public function configureReceiveInfrastructure();

    /**
     * @return TransportSubscriptionInfrastructure
     */
    abstract public function configureSubscriptionInfrastructure();

    /**
     * @param string $logicalAddress
     *
     * @return string
     */
    abstract public function toTransportAddress($logicalAddress);
}
