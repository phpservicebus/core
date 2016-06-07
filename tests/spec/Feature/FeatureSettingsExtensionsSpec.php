<?php

namespace spec\PSB\Core\Feature;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Feature\FeatureSettingsExtensions;
use PSB\Core\Feature\FeatureStateEnum;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\Util\Settings;

/**
 * @mixin FeatureSettingsExtensions
 */
class FeatureSettingsExtensionsSpec extends ObjectBehavior
{
    function it_enables_a_feature_by_default(Settings $settings)
    {
        $featureFqcn = 'irrelevant';
        $settings->tryGet(KnownSettingsEnum::FEATURE_FQCN_LIST)->willReturn([]);
        $settings->set(KnownSettingsEnum::FEATURE_FQCN_LIST, [$featureFqcn => $featureFqcn])->shouldBeCalled();
        $settings->setDefault($featureFqcn, FeatureStateEnum::ENABLED)->shouldBeCalled();

        self::enableFeatureByDefault($featureFqcn, $settings);
    }

    function it_enables_a_feature(Settings $settings)
    {
        $featureFqcn = 'irrelevant';
        $settings->tryGet(KnownSettingsEnum::FEATURE_FQCN_LIST)->willReturn([]);
        $settings->set(KnownSettingsEnum::FEATURE_FQCN_LIST, [$featureFqcn => $featureFqcn])->shouldBeCalled();
        $settings->set($featureFqcn, FeatureStateEnum::ENABLED)->shouldBeCalled();

        self::enableFeature($featureFqcn, $settings);
    }

    function it_registers_a_feature_when_no_other_features_are_registered(Settings $settings)
    {
        $featureFqcn = 'irrelevant';
        $settings->tryGet(KnownSettingsEnum::FEATURE_FQCN_LIST)->willReturn([]);
        $settings->set(KnownSettingsEnum::FEATURE_FQCN_LIST, [$featureFqcn => $featureFqcn])->shouldBeCalled();

        self::registerFeature($featureFqcn, $settings);
    }

    function it_registers_a_feature_when_other_features_are_registered(Settings $settings)
    {
        $featureFqcn = 'irrelevant';
        $settings->tryGet(KnownSettingsEnum::FEATURE_FQCN_LIST)->willReturn(['some' => 'other']);
        $settings->set(
            KnownSettingsEnum::FEATURE_FQCN_LIST,
            ['some' => 'other', $featureFqcn => $featureFqcn]
        )->shouldBeCalled();

        self::registerFeature($featureFqcn, $settings);
    }

    function it_disables_a_feature(Settings $settings)
    {
        $featureFqcn = 'irrelevant';
        $settings->set($featureFqcn, FeatureStateEnum::DISABLED)->shouldBeCalled();

        self::disableFeature($featureFqcn, $settings);
    }

    function it_marks_a_feature_as_inactive(Settings $settings)
    {
        $featureFqcn = 'irrelevant';
        $settings->set($featureFqcn, FeatureStateEnum::INACTIVE)->shouldBeCalled();

        self::markFeatureAsInactive($featureFqcn, $settings);
    }

    function it_marks_a_feature_as_active(Settings $settings)
    {
        $featureFqcn = 'irrelevant';
        $settings->set($featureFqcn, FeatureStateEnum::ACTIVE)->shouldBeCalled();

        self::markFeatureAsActive($featureFqcn, $settings);
    }

    function it_tells_if_a_feature_is_enabled(Settings $settings)
    {
        $featureFqcn = 'irrelevant';
        $settings->tryGet($featureFqcn)->willReturn(FeatureStateEnum::ENABLED);

        self::isFeatureEnabled($featureFqcn, $settings)->shouldReturn(true);
    }

    function it_tells_if_a_feature_is_active(Settings $settings)
    {
        $featureFqcn = 'irrelevant';
        $settings->tryGet($featureFqcn)->willReturn(FeatureStateEnum::ACTIVE);

        self::isFeatureActive($featureFqcn, $settings)->shouldReturn(true);
    }
}
