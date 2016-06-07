<?php
namespace PSB\Core\Transport;


use PSB\Core\Transport\Config\TransportInfrastructure;
use PSB\Core\Transport\Config\TransportSendInfrastructure;
use PSB\Core\Util\Settings;

class OutboundTransport
{
    /**
     * @param Settings $settings
     *
     * @return TransportSendInfrastructure
     */
    public function configure(Settings $settings)
    {
        /** @var TransportInfrastructure $transportInfrastructure */
        $transportInfrastructure = $settings->get(TransportInfrastructure::class);
        return $transportInfrastructure->configureSendInfrastructure();
    }
}
