<?php

namespace spec\PSB\Core\Feature\FeatureSpec;


use PSB\Core\Feature\Feature;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

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
