<?php

namespace spec\PSB\Core\Pipeline\Outgoing\StageContext;

use PhpSpec\ObjectBehavior;

use PSB\Core\Pipeline\Outgoing\StageContext\UnsubscribeContext;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\UnsubscribeOptions;

/**
 * @mixin UnsubscribeContext
 */
class UnsubscribeContextSpec extends ObjectBehavior
{
    function it_is_initializable(UnsubscribeOptions $options, PipelineStageContext $parentContext)
    {
        $this->beConstructedWith($irrelevantEvent = 'class', $options, $parentContext);
        $this->shouldHaveType('PSB\Core\Pipeline\Outgoing\StageContext\UnsubscribeContext');
    }

    function it_contains_the_event_set_at_construction(UnsubscribeOptions $options, PipelineStageContext $parentContext)
    {
        $this->beConstructedWith('event', $options, $parentContext);

        $this->getEventFqcn()->shouldReturn('event');
    }

    function it_contains_the_options_set_at_construction(
        UnsubscribeOptions $options,
        PipelineStageContext $parentContext
    ) {
        $this->beConstructedWith($irrelevantEvent = 'class', $options, $parentContext);

        $this->getUnsubscribeOptions()->shouldReturn($options);
    }

    function it_throws_if_event_is_empty_during_construction(
        UnsubscribeOptions $options,
        PipelineStageContext $parentContext
    ) {
        $this->beConstructedWith('', $options, $parentContext);

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }
}
