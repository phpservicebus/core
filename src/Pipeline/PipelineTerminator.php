<?php
namespace PSB\Core\Pipeline;


abstract class PipelineTerminator implements StageConnectorInterface, PipelineTerminatorInterface
{

    /**
     * @param PipelineStageContext $context
     *
     * @return void
     */
    abstract protected function terminate($context);

    /**
     * @param PipelineStageContext $context
     * @param callable             $next
     *
     * @return void
     */
    public function invoke($context, callable $next)
    {
        $this->terminate($context);
    }
}
