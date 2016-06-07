<?php
namespace PSB\Core\Feature;


class Prerequisite
{
    /**
     * @var callable
     */
    private $condition;

    /**
     * @var string
     */
    private $description;

    /**
     * @param callable $condition
     * @param string   $description
     */
    public function __construct(callable $condition, $description)
    {
        $this->condition = $condition;
        $this->description = $description;
    }

    /**
     * @return callable
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
