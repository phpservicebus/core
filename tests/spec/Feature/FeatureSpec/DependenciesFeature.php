<?php

namespace spec\PSB\Core\Feature\FeatureSpec;


use PSB\Core\Feature\Feature;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

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
