<?php
namespace PSB\Core\Pipeline;


use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Util\Guard;

class StepRegistration
{
    /**
     * @var string
     */
    private $stepId;

    /**
     * @var string
     */
    private $stepFqcn;

    /**
     * @var callable|null
     */
    private $factory;

    /**
     * @var null|string
     */
    private $description;

    /**
     * @var StepRegistrationDependency[]
     */
    private $befores = [];

    /**
     * @var StepRegistrationDependency[]
     */
    private $afters = [];

    /**
     * @param string        $stepId
     * @param string        $stepFqcn
     * @param callable|null $factory
     * @param string|null   $description
     */
    public function __construct($stepId, $stepFqcn, callable $factory = null, $description = null)
    {
        Guard::againstNullAndEmpty('stepId', $stepId);
        Guard::againstNullAndEmpty('stepClass', $stepFqcn);

        $this->stepId = $stepId;
        $this->stepFqcn = $stepFqcn;
        $this->factory = $factory;
        $this->description = $description;
    }

    /**
     * @param StepReplacement $replacement
     */
    public function replaceWith(StepReplacement $replacement)
    {
        $this->stepFqcn = $replacement->getStepFqcn();
        $this->description = $replacement->getDescription() ?: $this->description;
        $this->factory = $replacement->getFactory();
    }

    /**
     * @param BuilderInterface $builder
     */
    public function registerInBuilder(BuilderInterface $builder)
    {
        if ($this->factory) {
            $builder->defineSingleton($this->stepFqcn, $this->factory);
        }
    }

    /**
     * @return string
     */
    public function getStepId()
    {
        return $this->stepId;
    }

    /**
     * @return string
     */
    public function getStepFqcn()
    {
        return $this->stepFqcn;
    }

    /**
     * @return callable|null
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return StepRegistrationDependency[]
     */
    public function getBefores()
    {
        return $this->befores;
    }

    /**
     * @return StepRegistrationDependency[]
     */
    public function getAfters()
    {
        return $this->afters;
    }

    /**
     * @param string $id
     */
    public function insertBeforeIfExists($id)
    {
        Guard::againstNullAndEmpty('id', $id);
        $this->befores[] = new StepRegistrationDependency($this->stepId, $id, false);
    }

    /**
     * @param string $id
     */
    public function insertBefore($id)
    {
        Guard::againstNullAndEmpty('id', $id);
        $this->befores[] = new StepRegistrationDependency($this->stepId, $id, true);
    }

    /**
     * @param string $id
     */
    public function insertAfterIfExists($id)
    {
        Guard::againstNullAndEmpty('id', $id);
        $this->afters[] = new StepRegistrationDependency($this->stepId, $id, false);
    }

    /**
     * @param string $id
     */
    public function insertAfter($id)
    {
        Guard::againstNullAndEmpty('id', $id);
        $this->afters[] = new StepRegistrationDependency($this->stepId, $id, true);
    }
}
