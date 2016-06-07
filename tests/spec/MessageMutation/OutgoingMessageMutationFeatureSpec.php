<?php

namespace spec\PSB\Core\MessageMutation;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\MessageMutation\OutgoingMessageMutationFeature;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutationPipelineStep;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingPhysicalMessageMutationPipelineStep;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

/**
 * @mixin OutgoingMessageMutationFeature
 */
class OutgoingMessageMutationFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\MessageMutation\OutgoingMessageMutationFeature');
    }

    function it_describes_as_being_enabled_by_default()
    {
        $this->describe();
        $this->isEnabledByDefault()->shouldReturn(true);
    }

    function it_sets_up_by_registering_the_appropriate_steps(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $pipelineModifications->registerStep(
            'OutgoingLogicalMessageMutation',
            OutgoingLogicalMessageMutationPipelineStep::class,
            Argument::type('\Closure')
        )->shouldBeCalled();

        $pipelineModifications->registerStep(
            'OutgoingPhysicalMessageMutation',
            OutgoingPhysicalMessageMutationPipelineStep::class,
            Argument::type('\Closure')
        )->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
