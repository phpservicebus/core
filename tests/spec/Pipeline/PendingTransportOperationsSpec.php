<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Transport\TransportOperation;

/**
 * @mixin PendingTransportOperations
 */
class PendingTransportOperationsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\PendingTransportOperations');
    }

    function it_adds_one_operation(TransportOperation $transportOperation)
    {
        $this->add($transportOperation);
        $this->getOperations()->shouldReturn([$transportOperation]);
    }

    function it_adds_multiple_operations(TransportOperation $to1, TransportOperation $to2)
    {
        $this->addAll([$to1, $to2]);
        $this->getOperations()->shouldReturn([$to1, $to2]);
    }

    function it_tells_if_it_has_operations(TransportOperation $transportOperation)
    {
        $this->add($transportOperation);
        $this->hasOperations()->shouldBe(true);
    }
}
