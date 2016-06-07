<?php
namespace PSB\Core\Pipeline;


interface PipelineInterface
{
    /**
     * @param PipelineStageContextInterface $context
     */
    public function invoke(PipelineStageContextInterface $context);
}
