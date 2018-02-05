<?php
namespace spec\PSB\Core\Feature;

use PhpSpec\ObjectBehavior;
use PSB\Core\BusContextInterface;
use PSB\Core\Feature\RootFeature;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;
use spec\PSB\Core\Feature\FeatureSpec\ActivatableFeature;
use spec\PSB\Core\Feature\FeatureSpec\DefaultsFeature;
use spec\PSB\Core\Feature\FeatureSpec\DependenciesFeature;
use spec\PSB\Core\Feature\FeatureSpec\FailingPrerequisitesFeature;
use spec\PSB\Core\Feature\FeatureSpec\InstallTasksFeature;
use spec\PSB\Core\Feature\FeatureSpec\PassingPrerequisitesFeature;
use spec\PSB\Core\Feature\FeatureSpec\StartupTasksFeature;
use spec\PSB\Core\Feature\FeatureSpec\ThrowingOnMissingPrerequisiteParamsFeature;

/**
 * @mixin PassingPrerequisitesFeature
 */
class FeatureSpec extends ObjectBehavior
{
    function it_passes_prerequisites_check_if_all_prerequisites_are_met(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $this->beAnInstanceOf(PassingPrerequisitesFeature::class);
        $this->describe();
        $this->checkPrerequisites($settings, $builder, $pipelineModifications)->shouldBe(true);
    }

    function it_fails_prerequisites_check_if_at_least_one_prerequisite_fails(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $this->beAnInstanceOf(FailingPrerequisitesFeature::class);
        $this->describe();
        $this->checkPrerequisites($settings, $builder, $pipelineModifications)->shouldBe(false);
    }

    function it_passes_the_settings_builder_and_pipeline_modifications_to_the_prerequisite_callable(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $this->beAnInstanceOf(ThrowingOnMissingPrerequisiteParamsFeature::class);
        $this->describe();
        $this->shouldNotThrow()->duringCheckPrerequisites($settings, $builder, $pipelineModifications);
    }

    function it_gets_activated(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $this->beAnInstanceOf(ActivatableFeature::class);

        $settings->set('proof', 'value')->shouldBeCalled();

        $this->activate($settings, $builder, $pipelineModifications);
        $this->isActive()->shouldReturn(true);
    }

    function it_runs_the_registered_install_tasks(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $this->beAnInstanceOf(InstallTasksFeature::class);
        $this->setup($settings, $builder, $pipelineModifications);

        $settings->tryGet('something')->shouldBeCalledTimes(2);
        $this->install($builder);
    }

    function it_starts_the_registered_startup_tasks(BuilderInterface $builder, BusContextInterface $busContext)
    {
        $this->beAnInstanceOf(StartupTasksFeature::class);
        $this->describe();

        $busContext->subscribe('something')->shouldBeCalledTimes(2);
        $this->start($builder, $busContext);
    }

    function it_configures_registered_defaults(Settings $settings)
    {
        $this->beAnInstanceOf(DefaultsFeature::class);
        $this->describe();

        $settings->set('some1', 'value1')->shouldBeCalled();
        $settings->set('some2', 'value2')->shouldBeCalled();

        $this->configureDefaults($settings);
    }

    function it_declares_dependencies()
    {
        $this->beAnInstanceOf(DependenciesFeature::class);
        $this->describe();
        $this->getDependencies()->shouldReturn(
            [
                [DefaultsFeature::class],
                [StartupTasksFeature::class],
                [RootFeature::class, ActivatableFeature::class],
                [DefaultsFeature::class, StartupTasksFeature::class]
            ]
        );
    }

    function it_gets_the_name_of_the_feature_as_its_class_name()
    {
        $this->beAnInstanceOf(DependenciesFeature::class);
        $this->getName()->shouldReturn(DependenciesFeature::class);
    }
}
