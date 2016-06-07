<?php
namespace PSB\Core\Pipeline;


use PSB\Core\Transport\TransportOperation;

class PendingTransportOperations
{
    /**
     * @var TransportOperation[]
     */
    private $operations = [];

    /**
     * @param TransportOperation $transportOperation
     */
    public function add(TransportOperation $transportOperation)
    {
        $this->operations[] = $transportOperation;
    }

    /**
     * @param array $transportOperations
     */
    public function addAll(array $transportOperations)
    {
        foreach ($transportOperations as $transportOperation) {
            $this->add($transportOperation);
        }
    }

    /**
     * @return TransportOperation[]
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * @return bool
     */
    public function hasOperations()
    {
        return count($this->operations) > 0;
    }
}
