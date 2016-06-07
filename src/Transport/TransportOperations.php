<?php
namespace PSB\Core\Transport;


class TransportOperations
{
    /**
     * @var TransportOperation[]
     */
    private $transportOperations = [];

    /**
     * @param TransportOperation[] $transportOperations
     */
    public function __construct(array $transportOperations)
    {
        foreach ($transportOperations as $transportOperation) {
            $this->add($transportOperation);
        }
    }

    /**
     * @param TransportOperation $transportOperation
     */
    private function add(TransportOperation $transportOperation)
    {
        $this->transportOperations[] = $transportOperation;
    }

    /**
     * @return TransportOperation[]
     */
    public function getTransportOperations()
    {
        return $this->transportOperations;
    }
}
