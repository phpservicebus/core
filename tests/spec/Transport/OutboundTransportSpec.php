<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;

use PSB\Core\Transport\Config\TransportInfrastructure;
use PSB\Core\Transport\Config\TransportSendInfrastructure;
use PSB\Core\Transport\OutboundTransport;
use PSB\Core\Util\Settings;

/**
 * @mixin OutboundTransport
 */
class OutboundTransportSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\OutboundTransport');
    }

    function it_configures_send_infrastructure(
        Settings $setting,
        TransportInfrastructure $transportInfrastructure,
        TransportSendInfrastructure $sendInfrastructure
    ) {
        $setting->get(TransportInfrastructure::class)->willReturn($transportInfrastructure);
        $transportInfrastructure->configureSendInfrastructure()->willReturn($sendInfrastructure);

        $this->configure($setting)->shouldReturn($sendInfrastructure);
    }
}
