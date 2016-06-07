<?php

namespace spec\PSB\Core\Routing;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Routing\UnicastRoutingTable;

/**
 * @mixin UnicastRoutingTable
 */
class UnicastRoutingTableSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Routing\UnicastRoutingTable');
    }

    function it_registers_routing_rules()
    {
        $this->routeToEndpoint('fqcn1', 'endpoint1');
        $this->routeToEndpoint('fqcn2', 'endpoint2');

        $this->getEndpointNamesFor(['fqcn1', 'fqcn2'])->shouldReturn(['endpoint1', 'endpoint2']);
    }

    function it_registers_multiple_rules_for_the_same_message()
    {
        $this->routeToEndpoint('fqcn1', 'endpoint1');
        $this->routeToEndpoint('fqcn1', 'endpoint2');
        $this->routeToEndpoint('fqcn3', 'endpoint3');

        $this->getEndpointNamesFor(['fqcn1'])->shouldReturn(['endpoint1', 'endpoint2']);
    }

    function it_registers_multiple_rules_for_the_same_endpoint()
    {
        $this->routeToEndpoint('fqcn1', 'endpoint1');
        $this->routeToEndpoint('fqcn2', 'endpoint1');
        $this->routeToEndpoint('fqcn3', 'endpoint3');

        $this->getEndpointNamesFor(['fqcn1'])->shouldReturn(['endpoint1']);
        $this->getEndpointNamesFor(['fqcn2'])->shouldReturn(['endpoint1']);
        $this->getEndpointNamesFor(['fqcn3'])->shouldReturn(['endpoint3']);
    }

    function it_provides_unique_endpoint_names_even_if_registrations_may_lead_to_duplication()
    {
        $this->routeToEndpoint('fqcn1', 'endpoint1');
        $this->routeToEndpoint('fqcn1', 'endpoint1');
        $this->routeToEndpoint('fqcn1', 'endpoint2');
        $this->routeToEndpoint('fqcn2', 'endpoint2');

        $this->getEndpointNamesFor(['fqcn1', 'fqcn2'])->shouldReturn(['endpoint1', 'endpoint2']);
    }
}
