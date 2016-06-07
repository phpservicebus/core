<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Outgoing\ImmediateDispatchTerminator;
use PSB\Core\Pipeline\Outgoing\OutgoingPhysicalToDispatchConnector;
use PSB\Core\Pipeline\OutgoingPipelineFeature;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Transport\SendingFeature;
use PSB\Core\Util\Settings;

/**
 * @mixin OutgoingPipelineFeature
 */
class OutgoingPipelineFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\OutgoingPipelineFeature');
    }

    function it_describes_as_being_enabled_by_default_and_depending_on_sending_feature()
    {
        $this->describe();

        $this->isEnabledByDefault()->shouldReturn(true);
        $this->getDependencies()->shouldReturn([[SendingFeature::class]]);
    }

    function it_sets_up_by_registering_pipeline_steps(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $pipelineModifications->registerStep(
            'OutgoingPhysicalToDispatchConnector',
            OutgoingPhysicalToDispatchConnector::class,
            Argument::type('\Closure')
        )->shouldBeCalled();
        $pipelineModifications->registerStep(
            'ImmediateDispatchTerminator',
            ImmediateDispatchTerminator::class,
            Argument::type('\Closure')
        )->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
