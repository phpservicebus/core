<?php

namespace spec\PSB\Core\MessageMutation\Pipeline\Outgoing;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutationContext;

/**
 * @mixin OutgoingLogicalMessageMutationContext
 */
class OutgoingLogicalMessageMutationContextSpec extends ObjectBehavior
{
    private $messageMock;

    function let()
    {
        $this->messageMock = (object)['message'];
        $this->beConstructedWith($this->messageMock, []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutationContext');
    }

    function it_contains_the_message_set_at_construction()
    {
        $this->getMessage()->shouldReturn($this->messageMock);
    }

    function it_contains_the_headers_set_at_construction()
    {
        $this->getHeaders()->shouldReturn([]);
    }

    function it_allows_the_message_to_be_updated()
    {
        $newMessage = (object)['newmessage'];
        $this->updateMessage($newMessage);

        $this->getMessage()->shouldReturn($newMessage);
    }

    function it_allows_headers_to_be_set()
    {
        $this->setHeader('key', 'newvalue');

        $this->getHeaders()->shouldReturn(['key' => 'newvalue']);
    }

    function it_records_the_fact_that_the_message_has_been_updated()
    {
        $newMessage = (object)['newmessage'];
        $this->updateMessage($newMessage);

        $this->hasMessageChanged()->shouldReturn(true);
    }

    function it_does_not_record_a_message_change_if_message_has_not_been_changed()
    {
        $this->hasMessageChanged()->shouldReturn(false);
    }

    function it_throws_if_message_is_null_during_instantiation()
    {
        $this->beConstructedWith(null, []);

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_if_message_is_null_during_update()
    {
        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringUpdateMessage(null);
    }

    function it_throws_if_message_is_not_an_object_during_instantiation()
    {
        $this->beConstructedWith('', []);

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_if_message_is_not_an_object_during_update()
    {
        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringUpdateMessage('');
    }
}
