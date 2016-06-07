<?php
namespace PSB\Core\Feature;


use PSB\Core\KnownSettingsEnum;
use PSB\Core\Util\Settings;

class FeatureSettingsExtensions
{
    public static function enableFeatureByDefault($featureFqcn, Settings $settings)
    {
        static::registerFeature($featureFqcn, $settings);
        $settings->setDefault($featureFqcn, FeatureStateEnum::ENABLED);
    }

    public static function enableFeature($featureFqcn, Settings $settings)
    {
        static::registerFeature($featureFqcn, $settings);
        $settings->set($featureFqcn, FeatureStateEnum::ENABLED);
    }

    public static function registerFeature($featureFqcn, Settings $settings)
    {
        $fqcnList = $settings->tryGet(KnownSettingsEnum::FEATURE_FQCN_LIST) ?: [];
        $fqcnList[$featureFqcn] = $featureFqcn;
        $settings->set(KnownSettingsEnum::FEATURE_FQCN_LIST, $fqcnList);
    }

    public static function disableFeature($featureFqcn, Settings $settings)
    {
        $settings->set($featureFqcn, FeatureStateEnum::DISABLED);
    }

    public static function markFeatureAsActive($featureFqcn, Settings $settings)
    {
        $settings->set($featureFqcn, FeatureStateEnum::ACTIVE);
    }

    public static function markFeatureAsInactive($featureFqcn, Settings $settings)
    {
        $settings->set($featureFqcn, FeatureStateEnum::INACTIVE);
    }

    public static function isFeatureEnabled($featureFqcn, Settings $settings)
    {
        return $settings->tryGet($featureFqcn) == FeatureStateEnum::ENABLED;
    }

    public static function isFeatureActive($featureFqcn, Settings $settings)
    {
        return $settings->tryGet($featureFqcn) == FeatureStateEnum::ACTIVE;
    }
}
