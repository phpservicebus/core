<?php

namespace spec\PSB\Core\Pipeline\StepChainBuilderSpec;


use PSB\Core\Pipeline\PipelineStepInterface;

class SecondStageStep implements PipelineStepInterface
{
    public function invoke($context, callable $next)
    {
    }

    public static function getStageContextClass()
    {
        return 'SecondContext';
    }
}
