<?php

namespace spec\PSB\Core\MessageMutation;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\MessageMutation\IncomingMessageMutationFeature;
use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingLogicalMessageMutationPipelineStep;
use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingPhysicalMessageMutationPipelineStep;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

/**
 * @mixin IncomingMessageMutationFeature
 */
class IncomingMessageMutationFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\MessageMutation\IncomingMessageMutationFeature');
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
            'IncomingLogicalMessageMutation',
            IncomingLogicalMessageMutationPipelineStep::class,
            Argument::type('\Closure')
        )->shouldBeCalled();

        $pipelineModifications->registerStep(
            'IncomingPhysicalMessageMutation',
            IncomingPhysicalMessageMutationPipelineStep::class,
            Argument::type('\Closure')
        )->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
