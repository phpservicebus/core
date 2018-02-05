<?php

namespace spec\PSB\Core\Feature\FeatureSpec;


use PSB\Core\Feature\Feature;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

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
