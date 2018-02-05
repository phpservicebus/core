<?php

namespace spec\PSB\Core\Pipeline\StepChainBuilderSpec;


use PSB\Core\Pipeline\StageConnectorInterface;

class FirstToSecondStageConnector implements StageConnectorInterface
{
    public function invoke($context, callable $next)
    {
    }

    public static function getStageContextClass()
    {
        return 'FirstContext';
    }

    public static function getNextStageContextClass()
    {
        return 'SecondContext';
    }
}
