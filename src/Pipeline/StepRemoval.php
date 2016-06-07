<?php
namespace PSB\Core\Pipeline;


class StepRemoval
{
    /**
     * @var string
     */
    private $idToRemove;

    /**
     * @param string $idToRemove
     */
    public function __construct($idToRemove)
    {
        $this->idToRemove = $idToRemove;
    }

    /**
     * @return string
     */
    public function getIdToRemove()
    {
        return $this->idToRemove;
    }
}
