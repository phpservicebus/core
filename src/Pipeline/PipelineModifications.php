<?php
namespace PSB\Core\Pipeline;


use PSB\Core\ObjectBuilder\BuilderInterface;

class PipelineModifications
{
    /**
     * @var StepRegistration[]
     */
    private $additions = [];

    /**
     * @var StepRemoval[]
     */
    private $removals = [];

    /**
     * @var StepReplacement[]
     */
    private $replacements = [];

    /**
     * @param string $stepId
     *
     * @return StepRemoval
     */
    public function removeStep($stepId)
    {
        $removal = new StepRemoval($stepId);
        $this->removals[] = $removal;
        return $removal;
    }

    /**
     * @param string        $stepId
     * @param string        $stepFqcn
     * @param callable|null $factory
     * @param string|null   $description
     *
     * @return StepReplacement
     */
    public function replaceStep($stepId, $stepFqcn, callable $factory = null, $description = null)
    {
        $replacement = new StepReplacement($stepId, $stepFqcn, $factory, $description);
        $this->replacements[] = $replacement;
        return $replacement;
    }

    /**
     * @param string        $stepId
     * @param string        $stepFqcn
     * @param callable|null $factory
     * @param string|null   $description
     *
     * @return StepRegistration
     */
    public function registerStep($stepId, $stepFqcn, callable $factory = null, $description = null)
    {
        $registration = new StepRegistration($stepId, $stepFqcn, $factory, $description);
        $this->additions[] = $registration;
        return $registration;
    }

    /**
     * @param BuilderInterface $builder
     */
    public function registerStepsInBuilder(BuilderInterface $builder)
    {
        foreach ($this->replacements as $replacement) {
            $replacement->registerInBuilder($builder);
        }

        foreach ($this->additions as $registration) {
            $registration->registerInBuilder($builder);
        }
    }

    /**
     * @return StepRegistration[]
     */
    public function getAdditions()
    {
        return $this->additions;
    }

    /**
     * @return StepRemoval[]
     */
    public function getRemovals()
    {
        return $this->removals;
    }

    /**
     * @return StepReplacement[]
     */
    public function getReplacements()
    {
        return $this->replacements;
    }
}
