<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;
use PSB\Core\Pipeline\Pipeline;
use PSB\Core\Pipeline\PipelineStageContextInterface;
use spec\PSB\Core\Pipeline\PipelineSpec\ObservablePipelineConnector;
use spec\PSB\Core\Pipeline\PipelineSpec\ObservablePipelineStep;

/**
 * @mixin Pipeline
 */
class PipelineSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType('PSB\Core\Pipeline\Pipeline');
    }

    function it_does_nothing_if_there_are_no_steps_in_the_pipeline(PipelineStageContextInterface $context)
    {
        $this->beConstructedWith([]);
        $this->invoke($context);
    }

    function it_invokes_all_the_registered_steps_using_the_provided_context(PipelineStageContextInterface $context)
    {
        $steps = [];
        for ($i = 0; $i < 5; $i++) {
            $steps[] = new ObservablePipelineStep();
        }
        $this->beConstructedWith($steps);
        $context->getBuilder()->shouldBeCalledTimes(5);

        $this->invoke($context);
    }

    function it_invokes_all_the_registered_steps_and_connectors(
        PipelineStageContextInterface $context,
        PipelineStageContextInterface $connectorContext
    ) {
        $steps = [];
        for ($i = 0; $i < 5; $i++) {
            $steps[] = new ObservablePipelineStep();
        }
        $steps[] = new ObservablePipelineConnector($connectorContext->getWrappedObject());
        for ($i = 0; $i < 5; $i++) {
            $steps[] = new ObservablePipelineStep();
        }

        $this->beConstructedWith($steps);
        $context->getBuilder()->shouldBeCalledTimes(6);
        $connectorContext->getBuilder()->shouldBeCalledTimes(5);

        $this->invoke($context);
    }
}
