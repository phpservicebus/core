<?php

namespace spec\PSB\Core\Feature\FeatureSpec;


use PSB\Core\Feature\Feature;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

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
