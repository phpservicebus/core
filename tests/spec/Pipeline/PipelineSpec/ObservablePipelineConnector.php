<?php

namespace spec\PSB\Core\Pipeline\PipelineSpec;


use PSB\Core\Pipeline\StageConnectorInterface;

class ObservablePipelineConnector implements StageConnectorInterface
{
    /**
     * @var
     */
    private $nextContext;

    public function __construct($nextContext)
    {
        $this->nextContext = $nextContext;
    }

    public function invoke($context, callable $next)
    {
        $context->getBuilder();
        $next($this->nextContext);
    }

    public static function getStageContextClass()
    {
    }

    public static function getNextStageContextClass()
    {
    }
}
