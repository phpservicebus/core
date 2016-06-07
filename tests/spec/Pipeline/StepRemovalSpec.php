<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Pipeline\StepRemoval;

/**
 * @mixin StepRemoval
 */
class StepRemovalSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('id');
        $this->shouldHaveType('PSB\Core\Pipeline\StepRemoval');
    }

    function it_contains_the_id_set_at_construction()
    {
        $this->beConstructedWith('id');

        $this->getIdToRemove()->shouldReturn('id');
    }
}
