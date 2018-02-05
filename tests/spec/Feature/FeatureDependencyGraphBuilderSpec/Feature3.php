<?php

namespace spec\PSB\Core\Feature\FeatureDependencyGraphBuilderSpec;


class Feature3 extends Feature1
{
    public function describe()
    {
        $this->dependsOn('f1');
    }

    public function getName()
    {
        return 'f3';
    }
}
