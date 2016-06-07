<?php
namespace PSB\Core\Pipeline\Incoming;


use PSB\Core\Exception\InvalidArgumentException;

class IncomingLogicalMessage
{
    /**
     * @var object
     */
    private $messageInstance;

    /**
     * @var string
     */
    private $messageClass;

    /**
     * @var array
     */
    private $messageInterfaces;

    /**
     * @param object $messageInstance
     * @param string $messageClass
     * @param array  $messageInterfaces
     */
    public function __construct($messageInstance, $messageClass, array $messageInterfaces = [])
    {
        if (!is_object($messageInstance)) {
            throw new InvalidArgumentException('Message instance must be an object.');
        }

        $this->messageInstance = $messageInstance;
        $this->messageClass = $messageClass;
        $this->messageInterfaces = $messageInterfaces;
    }

    /**
     * @return object
     */
    public function getMessageInstance()
    {
        return $this->messageInstance;
    }

    /**
     * @return string
     */
    public function getMessageClass()
    {
        return $this->messageClass;
    }

    /**
     * @return array
     */
    public function getMessageInterfaces()
    {
        return $this->messageInterfaces;
    }

    /**
     * @return array
     */
    public function getMessageTypes()
    {
        return array_merge([$this->messageClass], $this->messageInterfaces);
    }

    /**
     * @param object                        $messageInstance
     * @param IncomingLogicalMessageFactory $helperFactory
     */
    public function updateInstance($messageInstance, IncomingLogicalMessageFactory $helperFactory)
    {
        if (!is_object($messageInstance)) {
            throw new InvalidArgumentException('Message instance must be an object.');
        }

        if ($messageInstance === $this->messageInstance) {
            return;
        }

        $helperLogicalMessage = $helperFactory->createFromObject($messageInstance);
        $this->messageInstance = $messageInstance;
        $this->messageClass = $helperLogicalMessage->getMessageClass();
        $this->messageInterfaces = $helperLogicalMessage->getMessageInterfaces();
    }
}
