<?php
namespace acceptancesupport\PSB\Core\Scenario;


class EndpointQueueStats
{
    /**
     * @var int
     */
    private $mainCount;

    /**
     * @var int
     */
    private $errorCount;

    /**
     * @param int $mainCount
     * @param int $errorCount
     */
    public function __construct($mainCount, $errorCount)
    {
        $this->mainCount = $mainCount;
        $this->errorCount = $errorCount;
    }

    /**
     * @return int
     */
    public function getMainCount()
    {
        return $this->mainCount;
    }

    /**
     * @return int
     */
    public function getErrorCount()
    {
        return $this->errorCount;
    }
}
