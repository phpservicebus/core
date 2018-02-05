<?php

namespace spec\PSB\Core\Pipeline\StepChainBuilderSpec;


use PSB\Core\Pipeline\PipelineTerminatorInterface;

class SecondStageTerminator implements PipelineTerminatorInterface
{
    public function invoke($context, callable $next)
    {
    }

    public static function getStageContextClass()
    {
        return 'SecondContext';
    }

    public static function getNextStageContextClass()
    {
        return '';
    }
}
