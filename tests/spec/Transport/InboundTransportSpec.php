<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Transport\Config\TransportInfrastructure;
use PSB\Core\Transport\Config\TransportReceiveInfrastructure;
use PSB\Core\Transport\InboundTransport;
use PSB\Core\Util\Settings;

/**
 * @mixin InboundTransport
 */
class InboundTransportSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\InboundTransport');
    }

    function it_configures_send_infrastructure(
        Settings $setting,
        TransportInfrastructure $transportInfrastructure,
        TransportReceiveInfrastructure $receiveInfrastructure
    ) {
        $setting->get(TransportInfrastructure::class)->willReturn($transportInfrastructure);
        $transportInfrastructure->configureReceiveInfrastructure()->willReturn($receiveInfrastructure);

        $this->configure($setting)->shouldReturn($receiveInfrastructure);
    }
}
