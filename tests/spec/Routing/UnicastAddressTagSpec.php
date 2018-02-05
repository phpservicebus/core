<?php

namespace spec\PSB\Core\Routing;

use PhpSpec\ObjectBehavior;

use PSB\Core\Routing\UnicastAddressTag;

/**
 * @mixin UnicastAddressTag
 */
class UnicastAddressTagSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('queuename');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Routing\UnicastAddressTag');
    }

    function it_contains_the_destination_set_at_construction()
    {
        $this->getDestination()->shouldReturn('queuename');
    }
}
