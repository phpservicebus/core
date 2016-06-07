<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Transport\TransportOperation;
use PSB\Core\Transport\TransportOperations;

/**
 * @mixin TransportOperations
 */
class TransportOperationsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([]);

        $this->shouldHaveType('PSB\Core\Transport\TransportOperations');
    }

    function it_gets_constructed_with_transport_operations(TransportOperation $to1, TransportOperation $to2)
    {
        $this->beConstructedWith([$to1->getWrappedObject(), $to2->getWrappedObject()]);

        $this->getTransportOperations()->shouldReturn([$to1->getWrappedObject(), $to2->getWrappedObject()]);
    }

    function it_throws_on_construction_if_operations_do_not_have_the_correct_type()
    {
        $this->beConstructedWith(['test']);

        $this->shouldThrow()->duringInstantiation();
    }
}
