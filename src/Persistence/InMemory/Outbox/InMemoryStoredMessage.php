<?php
namespace PSB\Core\Persistence\InMemory\Outbox;


use PSB\Core\Outbox\OutboxTransportOperation;

class InMemoryStoredMessage
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var OutboxTransportOperation[]
     */
    private $transportOperations;

    /**
     * @var bool
     */
    private $isDispatched = false;

    /**
     * @var \DateTime
     */
    private $storedAt;

    /**
     * InMemoryStoredMessage constructor.
     *
     * @param string                     $messageId
     * @param OutboxTransportOperation[] $transportOperations
     */
    public function __construct($messageId, array $transportOperations)
    {
        $this->id = $messageId;
        $this->transportOperations = $transportOperations;
        $this->storedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return OutboxTransportOperation[]
     */
    public function getTransportOperations()
    {
        return $this->transportOperations;
    }

    /**
     * @return boolean
     */
    public function isIsDispatched()
    {
        return $this->isDispatched;
    }

    /**
     * @return \DateTime
     */
    public function getStoredAt()
    {
        return $this->storedAt;
    }

    public function markAsDispatched()
    {
        $this->isDispatched = true;
        $this->transportOperations = [];
    }
}
