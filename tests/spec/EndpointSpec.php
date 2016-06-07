<?php

namespace spec\PSB\Core;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Endpoint;
use PSB\Core\EndpointConfigurator;
use PSB\Core\StartableEndpoint;

/**
 * @mixin Endpoint
 */
class EndpointSpec extends ObjectBehavior
{
    function it_builds_a_startable_endpoint(EndpointConfigurator $configurator, StartableEndpoint $startableEndpoint)
    {
        $configurator->build()->willReturn($startableEndpoint);
        self::build($configurator)->shouldReturn($startableEndpoint);
    }

    function it_builds_and_prepares_a_startable_endpoint(
        EndpointConfigurator $configurator,
        StartableEndpoint $startableEndpoint
    ) {
        $configurator->build()->willReturn($startableEndpoint);

        $startableEndpoint->prepare()->shouldBeCalled()->willReturn($startableEndpoint);
        self::prepare($configurator)->shouldReturn($startableEndpoint);
    }

    function it_builds_and_prepares_a_startable_endpoint_and_starts_it_as_an_endpoint_instance(
        EndpointConfigurator $configurator,
        StartableEndpoint $startableEndpoint
    ) {
        $configurator->build()->willReturn($startableEndpoint);

        $startableEndpoint->prepare()->shouldBeCalled()->willReturn($startableEndpoint);
        $startableEndpoint->start()->shouldBeCalled()->willReturn($startableEndpoint);
        self::start($configurator);
    }
}
