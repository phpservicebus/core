<?php
namespace PSB\Core\Pipeline\Outgoing;


use PSB\Core\Exception\InvalidArgumentException;

class OutgoingLogicalMessage
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
     * @param object $messageInstance
     * @param string $messageClass
     */
    public function __construct($messageInstance, $messageClass = null)
    {
        $this->assertObject($messageInstance);

        $this->messageInstance = $messageInstance;
        $this->messageClass = $messageClass;
        if (!$messageClass) {
            $this->messageClass = get_class($messageInstance);
        }
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
     * @param object $messageInstance
     */
    public function updateInstance($messageInstance)
    {
        $this->assertObject($messageInstance);

        $this->messageInstance = $messageInstance;
        $this->messageClass = get_class($messageInstance);
    }

    /**
     * @param mixed $messageInstance
     */
    private function assertObject($messageInstance) {
        if (!is_object($messageInstance)) {
            throw new InvalidArgumentException('Message instance must be an object.');
        }
    }
}
