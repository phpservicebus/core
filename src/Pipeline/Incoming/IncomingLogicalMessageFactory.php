<?php
namespace PSB\Core\Pipeline\Incoming;


use PSB\Core\Exception\InvalidArgumentException;

class IncomingLogicalMessageFactory
{
    /**
     * @param string $messageClass
     * @param object $message
     *
     * @return IncomingLogicalMessage
     */
    private function create($messageClass, $message)
    {
        $interfaces = array_values(class_implements($messageClass, true));
        return new IncomingLogicalMessage($message, $messageClass, $interfaces);
    }

    /**
     * @param object $message
     *
     * @return IncomingLogicalMessage
     * @throws InvalidArgumentException
     */
    public function createFromObject($message)
    {
        if (!is_object($message)) {
            throw new InvalidArgumentException('Message must be an object.');
        }

        return $this->create(get_class($message), $message);
    }
}
