<?php

namespace spec\PSB\Core\Pipeline\Outgoing;

use PhpSpec\ObjectBehavior;
use PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessage;
use spec\PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessageSpec\DummyMessage;
use spec\PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessageSpec\UpdatedDummyMessage;

/**
 * @mixin OutgoingLogicalMessage
 */
class OutgoingLogicalMessageSpec extends ObjectBehavior
{
    private $instanceMock;

    function let()
    {
        $this->instanceMock = new DummyMessage();
        $this->beConstructedWith($this->instanceMock);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessage');
    }

    function it_contains_the_instance_set_at_construction()
    {
        $this->getMessageInstance()->shouldReturn($this->instanceMock);
    }

    function it_contains_the_class_set_at_construction()
    {
        $this->getMessageClass()->shouldReturn(DummyMessage::class);
    }

    function it_accepts_an_override_for_the_message_class()
    {
        $this->beConstructedWith(new DummyMessage(), 'SomeOtherClass');
        $this->getMessageClass()->shouldReturn('SomeOtherClass');
    }

    function it_throws_on_construction_if_instance_is_not_an_object()
    {
        $this->beConstructedWith('');

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }

    function it_updates_the_instance_message()
    {
        $instance = new UpdatedDummyMessage();
        $this->updateInstance($instance);
        $this->getMessageClass()->shouldReturn(UpdatedDummyMessage::class);
        $this->getMessageInstance()->shouldReturn($instance);
    }

    function it_throws_on_updateing_the_message_instance_if_instance_is_not_an_object()
    {
        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringUpdateInstance('');
    }
}
