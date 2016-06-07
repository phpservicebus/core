<?php

namespace spec\PSB\Core\Outbox;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Outbox\OutboxTransportOperation;

/**
 * @mixin OutboxTransportOperation
 */
class OutboxTransportOperationSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('id', [], 'body', []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Outbox\OutboxTransportOperation');
    }

    function it_contains_the_message_id_set_at_construction()
    {
        $this->getMessageId()->shouldReturn('id');
    }

    function it_contains_the_options_set_at_construction()
    {
        $this->getOptions()->shouldReturn([]);
    }

    function it_contains_the_body_set_at_construction()
    {
        $this->getBody()->shouldReturn('body');
    }

    function it_contains_the_headers_set_at_construction()
    {
        $this->getHeaders()->shouldReturn([]);
    }

    function it_throws_if_message_id_null_or_empty_at_construction()
    {
        $this->beConstructedWith(null, [], 'body', []);
        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }
}
