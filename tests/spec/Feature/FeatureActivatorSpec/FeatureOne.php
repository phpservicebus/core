<?php

namespace spec\PSB\Core\Feature\FeatureActivatorSpec;


use PSB\Core\Feature\Feature;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class FeatureOne extends Feature
{
    public function describe()
    {
    }

    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
    }
}
