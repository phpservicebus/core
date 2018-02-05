<?php

namespace spec\PSB\Core\Persistence\InMemory\Outbox;

use PhpSpec\ObjectBehavior;

use PSB\Core\Outbox\OutboxMessage;
use PSB\Core\Persistence\InMemory\Outbox\InMemoryOutboxStorage;

/**
 * @mixin InMemoryOutboxStorage
 */
class InMemoryOutboxStorageSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Persistence\InMemory\Outbox\InMemoryOutboxStorage');
    }

    function it_returns_null_when_fetching_by_id_if_id_does_not_exist()
    {
        $this->get('id')->shouldReturn(null);
    }

    function it_can_store_a_message()
    {
        $message = new OutboxMessage('id', []);
        $this->store($message);

        $this->get('id')->shouldBeLike($message);
    }

    function it_throws_when_storing_if_message_with_id_already_exists()
    {
        $message = new OutboxMessage('id', []);
        $this->store($message);

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringStore($message);
    }

    function it_can_mark_message_as_dispatched_by_id_if_message_exists()
    {
        $message = new OutboxMessage('id', ['1', '2']);
        $this->store($message);
        $this->markAsDispatched('id');

        $this->get('id')->shouldBeLike(new OutboxMessage('id', []));
    }

    function it_fails_silently_if_attepting_to_mark_a_nonexistant_message_as_dispatched()
    {
        $this->markAsDispatched('id');
    }

    function it_rolls_back_the_last_store_operation()
    {
        $message = new OutboxMessage('id', ['1', '2']);
        $this->store($message);
        $this->rollBack();

        $this->get('id')->shouldBe(null);
    }
}
