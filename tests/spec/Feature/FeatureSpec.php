<?php
namespace spec\PSB\Core\Feature;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
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

namespace spec\PSB\Core\Feature\FeatureSpec;

use PSB\Core\BusContextInterface;
use PSB\Core\Feature\Feature;
use PSB\Core\Feature\FeatureInstallTaskInterface;
use PSB\Core\Feature\FeatureStartupTaskInterface;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class PassingPrerequisitesFeature extends Feature
{
    public function describe()
    {
        $this->registerPrerequisite(
            function () {
                return true;
            },
            'whatever'
        );
        $this->registerPrerequisite(
            function () {
                return true;
            },
            'whatever'
        );
    }

    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
    }
}

class FailingPrerequisitesFeature extends Feature
{
    public function describe()
    {
        $this->registerPrerequisite(
            function () {
                return true;
            },
            'whatever'
        );
        $this->registerPrerequisite(
            function () {
                return false;
            },
            'whatever'
        );
    }

    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
    }
}

class ThrowingOnMissingPrerequisiteParamsFeature extends Feature
{
    public function describe()
    {
        $this->registerPrerequisite(
            function (Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications) {
            },
            'whatever'
        );
    }

    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
    }
}

class ActivatableFeature extends Feature
{
    public function describe()
    {
    }

    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
        $settings->set('proof', 'value');
    }
}

class StartupTasksFeature extends Feature
{
    public function describe()
    {
        $this->registerStartupTask(
            function () {
                return new SampleStartupTask();
            }
        );
        $this->registerStartupTask(
            function () {
                return new SampleStartupTask();
            }
        );
    }

    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
    }
}

class SampleStartupTask implements FeatureStartupTaskInterface
{
    public function start(BusContextInterface $busContext)
    {
        $busContext->subscribe('something');
    }
}

class InstallTasksFeature extends Feature
{
    public function describe()
    {
    }

    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
        $this->registerInstallTask(
            function () use ($settings) {
                return new SampleInstallTask($settings);
            }
        );
        $this->registerInstallTask(
            function () use ($settings) {
                return new SampleInstallTask($settings);
            }
        );
    }
}

class SampleInstallTask implements FeatureInstallTaskInterface
{
    /**
     * @var Settings
     */
    private $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function install()
    {
        $this->settings->tryGet('something');
    }
}

class DefaultsFeature extends Feature
{
    public function describe()
    {
        $this->registerDefault(
            function (Settings $s) {
                $s->set('some1', 'value1');
            }
        );
        $this->registerDefault(
            function (Settings $s) {
                $s->set('some2', 'value2');
            }
        );
    }

    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
    }
}

class DependenciesFeature extends Feature
{
    public function describe()
    {
        $this->dependsOn(DefaultsFeature::class);
        $this->dependsOn(StartupTasksFeature::class);
        $this->dependsOnOptionally(ActivatableFeature::class);
        $this->dependsOnAtLeastOne([DefaultsFeature::class, StartupTasksFeature::class]);
    }

    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
    }
}

