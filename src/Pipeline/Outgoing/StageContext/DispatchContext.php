<?php
namespace PSB\Core\Pipeline\Outgoing\StageContext;


use PSB\Core\Exception\InvalidArgumentException;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\Transport\TransportOperation;

class DispatchContext extends PipelineStageContext
{
    /**
     * @var TransportOperation[]
     */
    private $transportOperations;

    /**
     * DispatchContext constructor.
     *
     * @param TransportOperation[] $transportOperations
     * @param PipelineStageContext $parentContext
     */
    public function __construct(array $transportOperations, PipelineStageContext $parentContext)
    {
        parent::__construct($parentContext);

        foreach ($transportOperations as $transportOperation) {
            if (!$transportOperation instanceof TransportOperation) {
                throw new InvalidArgumentException(
                    "Argument 1 should be an array of 'PSB\Core\\Transport\\TransportOperation'."
                );
            }
        }

        $this->transportOperations = $transportOperations;
    }

    /**
     * @return TransportOperation[]
     */
    public function getTransportOperations()
    {
        return $this->transportOperations;
    }
}
