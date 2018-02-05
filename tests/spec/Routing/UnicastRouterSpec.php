<?php

namespace spec\PSB\Core\Routing;

use PhpSpec\ObjectBehavior;
use PSB\Core\Routing\UnicastAddressTag;
use PSB\Core\Routing\UnicastRouter;
use PSB\Core\Routing\UnicastRoutingTable;
use PSB\Core\SendOptions;
use PSB\Core\Transport\Config\TransportInfrastructure;

/**
 * @mixin UnicastRouter
 */
class UnicastRouterSpec extends ObjectBehavior
{
    /**
     * @var UnicastRoutingTable
     */
    private $unicastRoutingTableMock;

    /**
     * @var TransportInfrastructure
     */
    private $transportInfrastructureMock;

    function let(UnicastRoutingTable $unicastRoutingTable, TransportInfrastructure $transportInfrastructure)
    {
        $this->unicastRoutingTableMock = $unicastRoutingTable;
        $this->transportInfrastructureMock = $transportInfrastructure;
        $this->beConstructedWith('localaddress', $unicastRoutingTable, $transportInfrastructure);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Routing\UnicastRouter');
    }

    function it_routes_to_local_instance_if_specified_by_send_options()
    {
        $options = new SendOptions();
        $options->routeToLocalEndpointInstance();

        $this->route($options, 'spec\PSB\Core\Routing\UnicastRouterSpec\Message')->shouldBeLike(
            [new UnicastAddressTag('localaddress')]
        );
    }

    function it_routes_to_explicit_destination_if_specified_by_send_options()
    {
        $options = new SendOptions();
        $options->setExplicitDestination('someaddress');

        $this->route($options, 'spec\PSB\Core\Routing\UnicastRouterSpec\Message')->shouldBeLike(
            [new UnicastAddressTag('someaddress')]
        );
    }

    function it_routes_to_explicit_destination_if_specified_by_send_options_even_if_set_to_route_to_local_instance()
    {
        $options = new SendOptions();
        $options->setExplicitDestination('someaddress');
        $options->routeToLocalEndpointInstance();

        $this->route($options, 'spec\PSB\Core\Routing\UnicastRouterSpec\Message')->shouldBeLike(
            [new UnicastAddressTag('someaddress')]
        );
    }

    function it_routes_to_multiple_destinations_based_on_the_routing_table_if_no_send_options_overrides_are_present()
    {
        $this->unicastRoutingTableMock->getEndpointNamesFor(['spec\PSB\Core\Routing\UnicastRouterSpec\Message'])->willReturn(
            ['some endpoint address']
        );
        $this->transportInfrastructureMock->toTransportAddress('some endpoint address')->willReturn('someaddress');

        $this->route(new SendOptions(), 'spec\PSB\Core\Routing\UnicastRouterSpec\Message')->shouldBeLike(
            [new UnicastAddressTag('someaddress')]
        );
    }
}
