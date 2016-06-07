<?php

namespace spec\PSB\Core\Feature;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Feature\Feature;
use PSB\Core\Feature\FeatureActivator;
use PSB\Core\Feature\FeatureStateEnum;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

/**
 * @mixin FeatureActivator
 */
class FeatureActivatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(new Settings());
        $this->shouldHaveType('PSB\Core\Feature\FeatureActivator');
    }

    function it_adds_features_to_be_activated(Settings $settings, Feature $feature)
    {
        $this->beConstructedWith($settings);

        $this->addFeature($feature);
        $this->getFeatures()->shouldReturn([$feature]);
    }

    function it_enables_the_feature_by_default_in_settings_when_adding_if_feature_is_enabled_by_default(
        Settings $settings,
        Feature $feature
    ) {
        $this->beConstructedWith($settings);
        $settings->tryGet(KnownSettingsEnum::FEATURE_FQCN_LIST)->willReturn([]);

        $feature->isEnabledByDefault()->willReturn(true);
        $featureFqcn = get_class($feature->getWrappedObject());

        $settings->setDefault($featureFqcn, FeatureStateEnum::ENABLED)->shouldBeCalled();
        $settings->set(KnownSettingsEnum::FEATURE_FQCN_LIST, [$featureFqcn => $featureFqcn])->shouldBeCalled();

        $this->addFeature($feature);
    }

    function it_configures_feature_defaults_when_activating_if_feature_is_enabled_in_settings(
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications,
        Feature $feature
    ) {
        $settings = new Settings();
        $this->beConstructedWith($settings);
        $feature->isEnabledByDefault()->willReturn(true);
        $feature->isActive()->willReturn(true);
        $feature->getName()->willReturn(get_class($feature));
        $feature->getDependencies()->willReturn([]);

        $this->addFeature($feature);

        $feature->configureDefaults($settings)->shouldBeCalled();

        $this->activateFeatures($builder, $pipelineModifications);
    }

    function it_inactivates_the_feature_in_settings_when_activating_if_it_does_not_meet_prerequisites(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications,
        Feature $feature
    ) {
        $this->beConstructedWith($settings);
        $settings->tryGet(get_class($feature->getWrappedObject()))->willReturn(FeatureStateEnum::ENABLED);
        $feature->isEnabledByDefault()->willReturn(false);
        $feature->isActive()->willReturn(false);
        $feature->getName()->willReturn(get_class($feature->getWrappedObject()));
        $feature->getDependencies()->willReturn([]);
        $feature->configureDefaults(Argument::any())->willReturn();
        $feature->checkPrerequisites($settings, $builder, $pipelineModifications)->willReturn(false);

        $settings->set(get_class($feature->getWrappedObject()), FeatureStateEnum::INACTIVE)->shouldBeCalled();

        $this->addFeature($feature);

        $this->activateFeatures($builder, $pipelineModifications);
    }

    function it_activates_the_feature_when_activating_if_it_meets_prerequisites(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications,
        Feature $feature
    ) {
        $this->beConstructedWith($settings);
        $settings->tryGet(get_class($feature->getWrappedObject()))->willReturn(FeatureStateEnum::ENABLED);
        $feature->isEnabledByDefault()->willReturn(false);
        $feature->isActive()->willReturn(false);
        $feature->getName()->willReturn(get_class($feature->getWrappedObject()));
        $feature->getDependencies()->willReturn([]);
        $feature->configureDefaults(Argument::any())->willReturn();
        $feature->checkPrerequisites($settings, $builder, $pipelineModifications)->willReturn(true);

        $settings->set(get_class($feature->getWrappedObject()), FeatureStateEnum::ACTIVE)->shouldBeCalled();
        $feature->activate($settings, $builder, $pipelineModifications)->shouldBeCalled();

        $this->addFeature($feature);

        $this->activateFeatures($builder, $pipelineModifications);
    }

    function it_inctivates_the_feature_when_activating_if_a_dependency_cannot_be_activated(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications,
        Feature $feature,
        Feature $dependencyFeature
    ) {
        $this->beConstructedWith($settings);
        $settings->tryGet(get_class($feature->getWrappedObject()))->willReturn(FeatureStateEnum::ENABLED);
        $settings->tryGet(get_class($dependencyFeature->getWrappedObject()))->willReturn(FeatureStateEnum::ENABLED);

        $dependencyFeature->isEnabledByDefault()->willReturn(false);
        $dependencyFeature->isActive()->willReturn(false);
        $dependencyFeature->getName()->willReturn(get_class($dependencyFeature->getWrappedObject()));
        $dependencyFeature->getDependencies()->willReturn([]);
        $dependencyFeature->configureDefaults(Argument::any())->willReturn();
        $dependencyFeature->checkPrerequisites($settings, $builder, $pipelineModifications)->willReturn(false);
        $feature->isEnabledByDefault()->willReturn(false);
        $feature->isActive()->willReturn(false);
        $feature->getName()->willReturn(get_class($feature->getWrappedObject()));
        $feature->getDependencies()->willReturn([[get_class($dependencyFeature->getWrappedObject())]]);
        $feature->configureDefaults(Argument::any())->willReturn();

        $settings->set(get_class($dependencyFeature->getWrappedObject()), FeatureStateEnum::INACTIVE)->shouldBeCalled();
        $settings->set(get_class($feature->getWrappedObject()), FeatureStateEnum::INACTIVE)->shouldBeCalled();

        $this->addFeature($feature);
        $this->addFeature($dependencyFeature);

        $this->activateFeatures($builder, $pipelineModifications);
    }

    function it_activates_the_feature_when_activating_if_all_dependencies_are_activated(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications,
        Feature $feature,
        Feature $dependencyFeature
    ) {
        $this->beConstructedWith($settings);
        $settings->tryGet(get_class($feature->getWrappedObject()))->willReturn(FeatureStateEnum::ENABLED);
        $settings->tryGet(get_class($dependencyFeature->getWrappedObject()))->willReturn(FeatureStateEnum::ENABLED);

        $dependencyFeature->isEnabledByDefault()->willReturn(false);
        $dependencyFeature->isActive()->willReturn(false);
        $dependencyFeature->getName()->willReturn(get_class($dependencyFeature->getWrappedObject()));
        $dependencyFeature->getDependencies()->willReturn([]);
        $dependencyFeature->configureDefaults(Argument::any())->willReturn();
        $dependencyFeature->checkPrerequisites($settings, $builder, $pipelineModifications)->willReturn(true);
        $dependencyFeature->activate($settings, $builder, $pipelineModifications)->will(
            function ($args) {
                $this->isActive()->willReturn(true);
            }
        );

        $feature->isEnabledByDefault()->willReturn(false);
        $feature->isActive()->willReturn(false);
        $feature->getName()->willReturn(get_class($feature->getWrappedObject()));
        $feature->getDependencies()->willReturn([[get_class($dependencyFeature->getWrappedObject())]]);
        $feature->configureDefaults(Argument::any())->willReturn();
        $feature->checkPrerequisites($settings, $builder, $pipelineModifications)->willReturn(true);

        $settings->set(get_class($dependencyFeature->getWrappedObject()), FeatureStateEnum::ACTIVE)->shouldBeCalled();
        $settings->set(get_class($feature->getWrappedObject()), FeatureStateEnum::ACTIVE)->shouldBeCalled();
        $feature->activate($settings, $builder, $pipelineModifications)->shouldBeCalled();

        $this->addFeature($feature);
        $this->addFeature($dependencyFeature);

        $this->activateFeatures($builder, $pipelineModifications);
    }
}
