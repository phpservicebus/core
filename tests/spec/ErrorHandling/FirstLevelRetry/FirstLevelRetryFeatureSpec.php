<?php

namespace spec\PSB\Core\ErrorHandling\FirstLevelRetry;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryFeature;
use PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryPolicy;
use PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryStorage;
use PSB\Core\ErrorHandling\FirstLevelRetry\Pipeline\FirstLevelRetryPipelineStep;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

/**
 * @mixin FirstLevelRetryFeature
 */
class FirstLevelRetryFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryFeature');
    }

    function it_describes_by_being_enabled_by_default()
    {
        $this->describe();

        $this->isEnabledByDefault()->shouldReturn(true);
    }

    function it_sets_up_by_registering_the_pipeline_step_and_its_collaborators(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $maxRetries = 6;
        $settings->tryGet(KnownSettingsEnum::MAX_FLR_RETRIES)->willReturn($maxRetries);

        $builder->defineSingleton(FirstLevelRetryStorage::class, new FirstLevelRetryStorage())->shouldBeCalled();
        $builder->defineSingleton(FirstLevelRetryPolicy::class, new FirstLevelRetryPolicy($maxRetries))
            ->shouldBeCalled();

        $pipelineModifications->registerStep(
            'FirstLevelRetryPipelineStep',
            FirstLevelRetryPipelineStep::class,
            Argument::type('\Closure')
        )->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }

    function it_sets_up_using_default_max_retries_if_none_set(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $settings->tryGet(KnownSettingsEnum::MAX_FLR_RETRIES)->willReturn(null);

        $builder->defineSingleton(FirstLevelRetryStorage::class, new FirstLevelRetryStorage())->shouldBeCalled();
        $builder->defineSingleton(FirstLevelRetryPolicy::class, new FirstLevelRetryPolicy(5))
            ->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
