<?php

namespace spec\PSB\Core\Routing;

use PhpSpec\ObjectBehavior;

use PSB\Core\Routing\MulticastAddressTag;

/**
 * @mixin MulticastAddressTag
 */
class MulticastAddressTagSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('MessageType');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Routing\MulticastAddressTag');
    }

    function it_contains_the_message_type_set_at_construction()
    {
        $this->getMessageType()->shouldReturn('MessageType');
    }
}
