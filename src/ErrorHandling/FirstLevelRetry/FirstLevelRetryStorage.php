<?php
namespace PSB\Core\ErrorHandling\FirstLevelRetry;


class FirstLevelRetryStorage
{
    /**
     * @var array
     */
    private $messageFailures = [];

    /**
     * @param string $messageId
     */
    public function clearFailuresForMessage($messageId)
    {
        if (isset($this->messageFailures[$messageId])) {
            unset($this->messageFailures[$messageId]);
        }
    }

    /**
     * @param string $messageId
     */
    public function incrementFailuresForMessage($messageId)
    {
        if (!isset($this->messageFailures[$messageId])) {
            $this->messageFailures[$messageId] = 0;
        }

        $this->messageFailures[$messageId]++;
    }

    /**
     * @param string $messageId
     *
     * @return int
     */
    public function getFailuresForMessage($messageId)
    {
        if (isset($this->messageFailures[$messageId])) {
            return $this->messageFailures[$messageId];
        }

        return 0;
    }

    public function clearAllFailures()
    {
        $this->messageFailures = [];
    }
}
