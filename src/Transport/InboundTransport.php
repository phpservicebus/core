<?php
namespace PSB\Core\Transport;


use PSB\Core\Transport\Config\TransportInfrastructure;
use PSB\Core\Transport\Config\TransportReceiveInfrastructure;
use PSB\Core\Util\Settings;

class InboundTransport
{
    /**
     * @param Settings $settings
     *
     * @return TransportReceiveInfrastructure
     */
    public function configure(Settings $settings)
    {
        /** @var TransportInfrastructure $transportInfrastructure */
        $transportInfrastructure = $settings->get(TransportInfrastructure::class);
        return $transportInfrastructure->configureReceiveInfrastructure();
    }
}
