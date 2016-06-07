<?php
namespace PSB\Core\Pipeline;


use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Util\Guard;

class StepReplacement
{
    /**
     * @var string
     */
    private $idToReplace;

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
     * StepReplacement constructor.
     *
     * @param string        $idToReplace
     * @param string        $stepFqcn
     * @param callable|null $factory
     * @param string|null   $description
     */
    public function __construct($idToReplace, $stepFqcn, callable $factory = null, $description = null)
    {
        Guard::againstNullAndEmpty('idToReplace', $idToReplace);
        Guard::againstNullAndEmpty('stepClass', $stepFqcn);

        $this->idToReplace = $idToReplace;
        $this->stepFqcn = $stepFqcn;
        $this->factory = $factory;
        $this->description = $description;
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
    public function getIdToReplace()
    {
        return $this->idToReplace;
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
}
