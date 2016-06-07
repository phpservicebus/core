<?php

namespace spec\PSB\Core\Pipeline\Outgoing\StageContext;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Pipeline\Outgoing\StageContext\SubscribeContext;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\SubscribeOptions;

/**
 * @mixin SubscribeContext
 */
class SubscribeContextSpec extends ObjectBehavior
{
    function it_is_initializable(SubscribeOptions $options, PipelineStageContext $parentContext)
    {
        $this->beConstructedWith($irrelevantEvent = 'class', $options, $parentContext);
        $this->shouldHaveType('PSB\Core\Pipeline\Outgoing\StageContext\SubscribeContext');
    }

    function it_contains_the_event_set_at_construction(SubscribeOptions $options, PipelineStageContext $parentContext)
    {
        $this->beConstructedWith('event', $options, $parentContext);

        $this->getEventFqcn()->shouldReturn('event');
    }

    function it_contains_the_options_set_at_construction(SubscribeOptions $options, PipelineStageContext $parentContext)
    {
        $this->beConstructedWith($irrelevantEvent = 'class', $options, $parentContext);

        $this->getSubscribeOptions()->shouldReturn($options);
    }

    function it_throws_if_event_is_empty_during_construction(
        SubscribeOptions $options,
        PipelineStageContext $parentContext
    ) {
        $this->beConstructedWith('', $options, $parentContext);

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }
}
