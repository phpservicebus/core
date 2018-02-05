<?php

namespace spec\PSB\Core\Pipeline\Outgoing\StageContext;

use PhpSpec\ObjectBehavior;
use PSB\Core\Pipeline\Outgoing\StageContext\DispatchContext;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\Transport\TransportOperation;

/**
 * @mixin DispatchContext
 */
class DispatchContextSpec extends ObjectBehavior
{
    function it_is_initializable(PipelineStageContext $parentContext)
    {
        $this->beConstructedWith([], $parentContext);

        $this->shouldHaveType('PSB\Core\Pipeline\Outgoing\StageContext\DispatchContext');
    }

    function it_contains_the_transport_operations_set_at_construction(
        TransportOperation $transportOperation,
        PipelineStageContext $parentContext
    ) {
        $this->beConstructedWith([$transportOperation], $parentContext);

        $this->getTransportOperations()->shouldReturn([$transportOperation]);
    }

    function it_throws_if_operations_do_not_have_the_correct_type(PipelineStageContext $parentContext)
    {
        $this->beConstructedWith(['dummy'], $parentContext);

        $this->shouldThrow()->duringInstantiation();
    }
}
