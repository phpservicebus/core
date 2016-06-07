<?php
namespace PSB\Core\Outbox;


use PSB\Core\Util\Guard;

class OutboxMessage
{
    /**
     * @var string
     */
    private $messageId;

    /**
     * @var OutboxTransportOperation[]
     */
    private $transportOperations;

    /**
     * @param string                     $messageId
     * @param OutboxTransportOperation[] $transportOperations
     */
    public function __construct($messageId, array $transportOperations)
    {
        Guard::againstNullAndEmpty('messageId', $messageId);

        $this->messageId = $messageId;
        $this->transportOperations = $transportOperations;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return OutboxTransportOperation[]
     */
    public function getTransportOperations()
    {
        return $this->transportOperations;
    }
}
