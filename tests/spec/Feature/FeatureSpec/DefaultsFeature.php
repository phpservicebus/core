<?php

namespace spec\PSB\Core\Feature\FeatureSpec;


use PSB\Core\Feature\Feature;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class DefaultsFeature extends Feature
{
    public function describe()
    {
        $this->registerDefault(
            function (Settings $s) {
                $s->set('some1', 'value1');
            }
        );
        $this->registerDefault(
            function (Settings $s) {
                $s->set('some2', 'value2');
            }
        );
    }

    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
    }
}
