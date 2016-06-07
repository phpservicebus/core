<?php

namespace PSB\Core\Feature;

use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Util\Settings;

class FeatureInstaller
{
    /**
     * @var Feature[]
     */
    private $features;

    /**
     * @param Feature[] $features
     */
    public function __construct(array $features)
    {
        $this->features = $features;
    }

    /**
     * @param BuilderInterface $builder
     * @param Settings         $settings
     */
    public function installFeatures(BuilderInterface $builder, Settings $settings)
    {
        if (!$settings->tryGet(KnownSettingsEnum::INSTALLERS_ENABLED)) {
            return;
        }

        foreach ($this->features as $feature) {
            if ($feature->isActive()) {
                $feature->install($builder);
            }
        }
    }
}
