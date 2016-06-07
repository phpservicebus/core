<?php
namespace PSB\Core\Feature;


use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class FeatureActivator
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Feature[]
     */
    private $features = [];

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param Feature $feature
     */
    public function addFeature(Feature $feature)
    {
        if ($feature->isEnabledByDefault()) {
            FeatureSettingsExtensions::enableFeatureByDefault(get_class($feature), $this->settings);
        }

        $this->features[] = $feature;
    }

    /**
     * @param BuilderInterface      $builder
     * @param PipelineModifications $pipelineModifications
     */
    public function activateFeatures(BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
        $sortedFeatures = $this->sort();
        $enabledFeatures = [];

        foreach ($sortedFeatures as $feature) {
            $featureFqcn = get_class($feature);
            if (FeatureSettingsExtensions::isFeatureEnabled($featureFqcn, $this->settings)) {
                $enabledFeatures[$featureFqcn] = $feature;
                $feature->configureDefaults($this->settings);
            }
        }

        foreach ($enabledFeatures as $feature) {
            $this->activateFeature($feature, $enabledFeatures, $builder, $pipelineModifications);
        }
    }

    /**
     * @return Feature[]
     */
    private function sort()
    {
        if (!$this->features) {
            return [];
        }

        $dependencyGraphBuilder = new FeatureDependencyGraphBuilder($this->features);
        return $dependencyGraphBuilder->build()->sort();
    }

    /**
     * @param Feature               $feature
     * @param Feature[]             $enabledFeatures
     * @param BuilderInterface      $builder
     * @param PipelineModifications $pipelineModifications
     *
     * @return bool
     */
    private function activateFeature(
        Feature $feature,
        $enabledFeatures,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        if ($feature->isActive()) {
            return true;
        }

        $dependencyGroups = $feature->getDependencies();
        $groupsActivationStatus = [];

        foreach ($dependencyGroups as $dependencyGroup) {
            $dependentFeaturesToActivate = [];
            foreach ($dependencyGroup as $dependencyFqcn) {
                if (isset($enabledFeatures[$dependencyFqcn])) {
                    $dependentFeaturesToActivate[$dependencyFqcn] = $enabledFeatures[$dependencyFqcn];
                }
            }

            $dependenciesActivationStatus = [];
            foreach ($dependentFeaturesToActivate as $featureFqcn => $dependentFeature) {
                $dependenciesActivationStatus[$featureFqcn] = $this->activateFeature(
                    $dependentFeature,
                    $enabledFeatures,
                    $builder,
                    $pipelineModifications
                );
            }

            // if at least one dependency in the group has been activated
            $groupsActivationStatus[] = in_array(true, $dependenciesActivationStatus);
        }

        $featureFqcn = get_class($feature);

        // if all groups have been activated
        if (!in_array(false, $groupsActivationStatus)) {
            if (!$feature->checkPrerequisites($this->settings, $builder, $pipelineModifications)) {
                FeatureSettingsExtensions::markFeatureAsInactive($featureFqcn, $this->settings);
                return false;
            }

            FeatureSettingsExtensions::markFeatureAsActive($featureFqcn, $this->settings);
            $feature->activate($this->settings, $builder, $pipelineModifications);
            return true;
        }

        FeatureSettingsExtensions::markFeatureAsInactive($featureFqcn, $this->settings);
        return false;
    }

    /**
     * @return Feature[]
     */
    public function getFeatures()
    {
        return $this->features;
    }
}
