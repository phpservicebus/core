<?php

namespace spec\PSB\Core\Feature\FeatureSpec;


use PSB\Core\Feature\FeatureInstallTaskInterface;
use PSB\Core\Util\Settings;

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
