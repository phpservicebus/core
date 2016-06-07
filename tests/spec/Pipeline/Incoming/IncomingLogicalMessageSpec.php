<?php

namespace spec\PSB\Core\Pipeline\Incoming;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessage;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactory;
use spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageSpec\DumbObject;
use spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageSpec\DumbObjectImplementingDumbInterface;

/**
 * @mixin IncomingLogicalMessage
 */
class IncomingLogicalMessageSpec extends ObjectBehavior
{
    private $instanceMock;

    function let()
    {
        $this->instanceMock = (object)[];
        $this->beConstructedWith($this->instanceMock, 'Class', ['Interface1', 'Interface2']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Incoming\IncomingLogicalMessage');
    }

    function it_contains_the_instance_set_at_construction()
    {
        $this->getMessageInstance()->shouldReturn($this->instanceMock);
    }

    function it_contians_the_class_set_at_construction()
    {
        $this->getMessageClass()->shouldReturn('Class');
    }

    function it_contains_the_interfaces_set_at_construction()
    {
        $this->getMessageInterfaces()->shouldReturn(['Interface1', 'Interface2']);
    }

    function it_provides_all_types_set_at_construction()
    {
        $this->getMessageTypes()->shouldReturn(['Class', 'Interface1', 'Interface2']);
    }

    function it_throws_if_instance_is_not_an_object()
    {
        $this->beConstructedWith('', '', []);

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }

    function it_can_update_the_message_instance_if_it_is_different(
        IncomingLogicalMessageFactory $factory,
        IncomingLogicalMessage $helperLogicalMessage
    ) {
        $message = new DumbObject();
        $this->beConstructedWith($message, 'spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageSpec\DumbObject', []);

        $newMessage = new DumbObjectImplementingDumbInterface();
        $factory->createFromObject($newMessage)->willReturn($helperLogicalMessage);
        $helperLogicalMessage->getMessageClass()->willReturn(
            'spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageSpec\DumbObjectImplementingDumbInterface'
        );
        $helperLogicalMessage->getMessageInterfaces()->willReturn(
            ['spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageSpec\DumbInterface']
        );

        $this->updateInstance($newMessage, $factory);

        $this->getMessageInstance()->shouldReturn($newMessage);
        $this->getMessageClass()->shouldReturn(
            'spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageSpec\DumbObjectImplementingDumbInterface'
        );
        $this->getMessageInterfaces()->shouldReturn(
            ['spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageSpec\DumbInterface']
        );
    }

    function it_does_no_update_if_the_message_instance_is_the_same(IncomingLogicalMessageFactory $factory)
    {
        $message = new DumbObject();
        $this->beConstructedWith($message, 'spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageSpec\DumbObject', []);

        $this->updateInstance($message, $factory);

        $factory->createFromObject(Argument::any())->shouldNotBeCalled();
        $this->getMessageInstance()->shouldReturn($message);
        $this->getMessageClass()->shouldReturn(
            'spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageSpec\DumbObject'
        );
        $this->getMessageInterfaces()->shouldReturn([]);
    }

    function it_throws_on_update_if_message_is_not_an_object(IncomingLogicalMessageFactory $factory)
    {
        $message = new DumbObject();
        $this->beConstructedWith($message, 'spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageSpec\DumbObject', []);

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringUpdateInstance('', $factory);
    }
}

namespace spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageSpec;

class DumbObject
{
}

interface DumbInterface
{
}

class DumbObjectImplementingDumbInterface implements DumbInterface
{
}
