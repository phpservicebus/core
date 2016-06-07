<?php
namespace PSB\Core\Pipeline;


use PSB\Core\Exception\PipelineBuildingException;
use PSB\Core\Util\DependencyGraph\DependencyGraph;
use PSB\Core\Util\DependencyGraph\GraphBuilderInterface;

class StepDependencyGraphBuilder implements GraphBuilderInterface
{
    /**
     * @var StepRegistration[]
     */
    private $steps;

    /**
     * @var StepRegistration[]
     */
    private $idToStep = [];

    /**
     * The actual graph maintained as an array of arrays (node id to successor ids).
     *
     * @var [][]
     */
    private $idDirectedGraph = [];

    /**
     * @param StepRegistration[] $steps
     */
    public function __construct($steps)
    {
        $this->steps = $steps;
    }

    /**
     * @return DependencyGraph
     */
    public function build()
    {
        // prepare the directed graph lists and id to step mapping
        foreach ($this->steps as $step) {
            $this->idDirectedGraph[$step->getStepId()] = [];
            $this->idToStep[$step->getStepId()] = $step;
        }

        // build the actual graph from before and after dependencies
        foreach ($this->steps as $step) {
            $this->addBeforesToDirectedGraph($step);
            $this->addAftersToDirectedGraph($step);
        }

        return new DependencyGraph($this->idToStep, $this->idDirectedGraph);
    }

    /**
     * @param StepRegistration $currentStep
     *
     * @throws PipelineBuildingException
     */
    private function addBeforesToDirectedGraph(StepRegistration $currentStep)
    {
        foreach ($currentStep->getBefores() as $before) {
            if (!isset($this->idToStep[$before->getDependsOnId()]) && $before->isEnforced()) {
                $allStepIds = implode(',', array_keys($this->idToStep));
                throw new PipelineBuildingException(
                    "Registration '{$before->getDependsOnId()}' specified in the insertbefore of the '{$currentStep->getStepId()}' step does not exist. Current step ids: $allStepIds."
                );
            }

            if (isset($this->idToStep[$before->getDependsOnId()])) {
                $this->idDirectedGraph[$before->getDependantId()][] = $before->getDependsOnId();
            }
        }
    }

    /**
     * @param StepRegistration $currentStep
     *
     * @throws PipelineBuildingException
     */
    private function addAftersToDirectedGraph(StepRegistration $currentStep)
    {
        foreach ($currentStep->getAfters() as $after) {
            if (!isset($this->idToStep[$after->getDependsOnId()]) && $after->isEnforced()) {
                $allStepIds = implode(',', array_keys($this->idToStep));
                throw new PipelineBuildingException(
                    "Registration '{$after->getDependsOnId()}' specified in the insertafter of the '{$currentStep->getStepId()}' step does not exist. Current step ids: $allStepIds."
                );
            }

            if (isset($this->idToStep[$after->getDependsOnId()])) {
                $this->idDirectedGraph[$after->getDependsOnId()][] = $after->getDependantId();
            }
        }
    }
}
