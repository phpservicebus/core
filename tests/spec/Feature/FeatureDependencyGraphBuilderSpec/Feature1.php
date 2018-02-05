<?php

namespace spec\PSB\Core\Feature\FeatureDependencyGraphBuilderSpec;


use PSB\Core\Feature\Feature;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class Feature1 extends Feature
{
    public function describe()
    {
        $this->dependsOn('f2');
    }

    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
    }

    public function getName()
    {
        return 'f1';
    }
}
