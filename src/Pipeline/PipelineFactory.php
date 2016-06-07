<?php
namespace PSB\Core\Pipeline;


use PSB\Core\ObjectBuilder\BuilderInterface;

class PipelineFactory
{
    /**
     * @var BuilderInterface
     */
    private $builder;

    /**
     * @var StepChainBuilderFactory
     */
    private $chainBuilderFactory;

    /**
     * @param BuilderInterface        $builder
     * @param StepChainBuilderFactory $chainBuilderFactory
     */
    public function __construct(BuilderInterface $builder, StepChainBuilderFactory $chainBuilderFactory)
    {
        $this->builder = $builder;
        $this->chainBuilderFactory = $chainBuilderFactory;
    }

    /**
     * @param string                $stageContextFqcn
     * @param PipelineModifications $pipelineModifications
     *
     * @return PipelineInterface
     */
    public function createStartingWith($stageContextFqcn, PipelineModifications $pipelineModifications)
    {
        $stepChainBuilder = $this->chainBuilderFactory->createChainBuilder($stageContextFqcn, $pipelineModifications);

        $stepRegistrations = $stepChainBuilder->build();
        $stepInstances = [];
        foreach ($stepRegistrations as $registration) {
            $stepInstances[] = $this->builder->build($registration->getStepFqcn());
        }

        return new Pipeline($stepInstances);
    }
}
