<?php

namespace spec\PSB\Core\Pipeline\Incoming;

use PhpSpec\ObjectBehavior;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactory;
use spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactorySpec\DumbObject;
use spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactorySpec\DumbObjectImplementingDumbInterface;

/**
 * @mixin IncomingLogicalMessageFactory
 */
class IncomingLogicalMessageFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactory');
    }

    function it_can_create_logical_message_from_object()
    {
        $this->createFromObject((object)[])->shouldBeAnInstanceOf('PSB\Core\Pipeline\Incoming\IncomingLogicalMessage');
    }

    function it_throws_if_message_is_not_an_object()
    {
        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringCreateFromObject('');
    }

    function it_creates_a_correct_logical_message_from_object_that_does_not_implement_interfaces()
    {
        $object = new DumbObject();
        $logicalMessage = $this->createFromObject($object);

        $logicalMessage->getMessageClass()->shouldReturn(
            'spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactorySpec\DumbObject'
        );
        $logicalMessage->getMessageInterfaces()->shouldReturn([]);
    }

    function it_creates_a_correct_logical_message_from_object_that_does_implement_interfaces()
    {
        $object = new DumbObjectImplementingDumbInterface();
        $logicalMessage = $this->createFromObject($object);

        $logicalMessage->getMessageClass()->shouldReturn(
            'spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactorySpec\DumbObjectImplementingDumbInterface'
        );
        $logicalMessage->getMessageInterfaces()->shouldReturn(
            ['spec\PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactorySpec\DumbInterface']
        );
    }
}
