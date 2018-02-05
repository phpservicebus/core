<?php

namespace spec\PSB\Core\Feature\FeatureSpec;


use PSB\Core\BusContextInterface;
use PSB\Core\Feature\FeatureStartupTaskInterface;

class SampleStartupTask implements FeatureStartupTaskInterface
{
    public function start(BusContextInterface $busContext)
    {
        $busContext->subscribe('something');
    }
}
