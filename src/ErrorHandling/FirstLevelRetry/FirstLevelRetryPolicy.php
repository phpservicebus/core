<?php
namespace PSB\Core\ErrorHandling\FirstLevelRetry;


class FirstLevelRetryPolicy
{
    /**
     * @var int
     */
    private $maxRetries;

    /**
     * @param int $masRetries
     */
    public function __construct($masRetries)
    {
        $this->maxRetries = $masRetries;
    }

    /**
     * @param int $numberOfRetries
     *
     * @return bool
     */
    public function shouldGiveUp($numberOfRetries)
    {
        return $this->maxRetries <= $numberOfRetries;
    }
}
