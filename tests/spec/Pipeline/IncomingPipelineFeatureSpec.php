<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Incoming\InvokeHandlerTerminator;
use PSB\Core\Pipeline\Incoming\LoadHandlersConnector;
use PSB\Core\Pipeline\IncomingPipelineFeature;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

/**
 * @mixin IncomingPipelineFeature
 */
class IncomingPipelineFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\IncomingPipelineFeature');
    }

    function it_describes_as_being_enabled_by_default()
    {
        $this->describe();

        $this->isEnabledByDefault()->shouldReturn(true);
    }

    function it_sets_up_by_registering_pipeline_steps(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $pipelineModifications->registerStep(
            'LoadHandlersConnector',
            LoadHandlersConnector::class,
            Argument::type('\Closure')
        )->shouldBeCalled();
        $pipelineModifications->registerStep(
            'InvokeHandlerTerminator',
            InvokeHandlerTerminator::class,
            Argument::type('\Closure')
        )->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
