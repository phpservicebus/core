<?php
namespace PSB\Core\Transport;


use PSB\Core\Util\Guard;

class PushSettings
{
    /**
     * @var string
     */
    private $inputQueue;

    /**
     * @var string
     */
    private $errorQueue;

    /**
     * @var bool
     */
    private $purgeOnStartup;

    /**
     * @param string $inputQueue
     * @param string $errorQueue
     * @param bool   $purgeOnStartup
     */
    public function __construct($inputQueue, $errorQueue, $purgeOnStartup)
    {
        Guard::againstNullAndEmpty('inputQueue', $inputQueue);
        Guard::againstNullAndEmpty('errorQueue', $errorQueue);

        $this->inputQueue = $inputQueue;
        $this->errorQueue = $errorQueue;
        $this->purgeOnStartup = $purgeOnStartup;
    }

    /**
     * @return string
     */
    public function getInputQueue()
    {
        return $this->inputQueue;
    }

    /**
     * @return string
     */
    public function getErrorQueue()
    {
        return $this->errorQueue;
    }

    /**
     * @return boolean
     */
    public function isPurgeOnStartup()
    {
        return $this->purgeOnStartup;
    }
}
