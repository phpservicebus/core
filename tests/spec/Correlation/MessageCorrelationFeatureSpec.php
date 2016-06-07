<?php

namespace spec\PSB\Core\Correlation;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Correlation\MessageCorrelationFeature;
use PSB\Core\Correlation\Pipeline\AttachCorrelationIdPipelineStep;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

/**
 * @mixin MessageCorrelationFeature
 */
class MessageCorrelationFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Correlation\MessageCorrelationFeature');
    }

    function it_describes_as_being_enabled_by_default()
    {
        $this->describe();
        $this->isEnabledByDefault()->shouldBe(true);
    }

    function it_sets_up_by_registering_the_pipeline_step(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $pipelineModifications->registerStep(
            'AttachCorrelationIdPipelineStep',
            AttachCorrelationIdPipelineStep::class,
            Argument::type('\Closure')
        )->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
