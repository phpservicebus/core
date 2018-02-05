<?php

namespace spec\PSB\Core\Outbox;

use PhpSpec\ObjectBehavior;

use PSB\Core\Outbox\OutboxMessage;

/**
 * @mixin OutboxMessage
 */
class OutboxMessageSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('id', []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Outbox\OutboxMessage');
    }

    function it_contains_the_message_id_set_at_construction()
    {
        $this->getMessageId()->shouldReturn('id');
    }

    function it_contains_the_operations_set_at_construction()
    {
        $this->getTransportOperations()->shouldReturn([]);
    }

    function it_throws_if_message_id_null_or_empty_at_construction()
    {
        $this->beConstructedWith(null, []);
        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }
}
