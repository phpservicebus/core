<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Transport\QueueBindings;

/**
 * @mixin QueueBindings
 */
class QueueBindingsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\QueueBindings');
    }

    function it_can_bind_receiving_addreses()
    {
        $this->bindReceiving('address1');
        $this->bindReceiving('address2');

        $this->getReceivingAddresses()->shouldReturn(['address1', 'address2']);
    }

    function it_can_bind_sending_addreses()
    {
        $this->bindSending('address1');
        $this->bindSending('address2');

        $this->getSendingAddresses()->shouldReturn(['address1', 'address2']);
    }
}
