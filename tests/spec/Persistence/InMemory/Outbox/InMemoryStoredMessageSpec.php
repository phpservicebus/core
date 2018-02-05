<?php

namespace spec\PSB\Core\Persistence\InMemory\Outbox;

use PhpSpec\ObjectBehavior;

use PSB\Core\Persistence\InMemory\Outbox\InMemoryStoredMessage;

/**
 * @mixin InMemoryStoredMessage
 */
class InMemoryStoredMessageSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('id', []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Persistence\InMemory\Outbox\InMemoryStoredMessage');
    }

    function it_contains_the_id_set_at_construction()
    {
        $this->getId()->shouldReturn('id');
    }

    function it_contains_the_operations_set_at_construction()
    {
        $this->getTransportOperations()->shouldReturn([]);
    }

    function it_is_not_dispatched_at_construction()
    {
        $this->isIsDispatched()->shouldReturn(false);
    }

    function it_has_timestamp_initialized_at_construction()
    {
        $this->getStoredAt()->shouldHaveType('\DateTime');
    }

    function it_can_be_marked_as_dispatched()
    {
        $this->markAsDispatched();

        $this->isIsDispatched()->shouldReturn(true);
    }
}
