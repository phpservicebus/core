<?php
namespace PSB\Core\Pipeline;


class StepChainBuilderFactory
{
    /**
     * @param string                $stageContextFqcn
     * @param PipelineModifications $pipelineModifications
     *
     * @return StepChainBuilder
     */
    public function createChainBuilder($stageContextFqcn, PipelineModifications $pipelineModifications)
    {
        return new StepChainBuilder(
            $stageContextFqcn,
            $pipelineModifications->getAdditions(),
            $pipelineModifications->getReplacements(),
            $pipelineModifications->getRemovals()
        );
    }
}
