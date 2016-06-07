<?php
namespace PSB\Core\Pipeline;


use PSB\Core\Util\Guard;

class StepRegistrationDependency
{
    /**
     * @var string
     */
    private $dependantId;

    /**
     * @var string
     */
    private $dependsOnId;

    /**
     * @var bool
     */
    private $isEnforced;

    /**
     * @param string $dependantId
     * @param string $dependsOnId
     * @param bool   $isEnforced
     */
    public function __construct($dependantId, $dependsOnId, $isEnforced)
    {
        Guard::againstNullAndEmpty('dependantId', $dependantId);
        Guard::againstNullAndEmpty('dependsOnId', $dependsOnId);

        $this->dependantId = $dependantId;
        $this->dependsOnId = $dependsOnId;
        $this->isEnforced = $isEnforced;
    }

    /**
     * @return string
     */
    public function getDependantId()
    {
        return $this->dependantId;
    }

    /**
     * @return string
     */
    public function getDependsOnId()
    {
        return $this->dependsOnId;
    }

    /**
     * @return boolean
     */
    public function isEnforced()
    {
        return $this->isEnforced;
    }
}
