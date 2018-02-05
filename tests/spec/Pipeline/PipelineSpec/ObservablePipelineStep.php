<?php

namespace spec\PSB\Core\Pipeline\PipelineSpec;


use PSB\Core\Pipeline\PipelineStepInterface;

class ObservablePipelineStep implements PipelineStepInterface
{
    public function invoke($context, callable $next)
    {
        $context->getBuilder();
        $next();
    }

    public static function getStageContextClass()
    {
    }
}
