<?php
namespace PSB\Core\Persistence\InMemory\Outbox;


use PSB\Core\Exception\InvalidArgumentException;
use PSB\Core\Outbox\OutboxMessage;
use PSB\Core\Outbox\OutboxStorageInterface;

class InMemoryOutboxStorage implements OutboxStorageInterface
{
    /**
     * @var InMemoryStoredMessage[]
     */
    private $messages = [];

    /**
     * @var string
     */
    private $lastMessageId;

    /**
     * Fetches the given message from the storage. It returns null if no message is found.
     *
     * @param string $messageId
     *
     * @return OutboxMessage|null
     */
    public function get($messageId)
    {
        if (!isset($this->messages[$messageId])) {
            return null;
        }

        return new OutboxMessage($messageId, $this->messages[$messageId]->getTransportOperations());
    }

    /**
     * Stores the message to enable deduplication and re-dispatching of transport operations.
     * Throws an exception if a message with the same ID already exists.
     *
     * @param OutboxMessage $message
     *
     * @return void
     */
    public function store(OutboxMessage $message)
    {
        $messageId = $message->getMessageId();
        if (isset($this->messages[$messageId])) {
            throw new InvalidArgumentException("Outbox message with ID '$messageId' already exists in storage.");
        }

        $this->messages[$messageId] = new InMemoryStoredMessage($messageId, $message->getTransportOperations());
        $this->lastMessageId = $messageId;
    }

    /**
     * @param string $messageId
     *
     * @return void
     */
    public function markAsDispatched($messageId)
    {
        if (!isset($this->messages[$messageId])) {
            return;
        }

        $this->messages[$messageId]->markAsDispatched();
    }

    /**
     * Initiates the transaction
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function beginTransaction()
    {
        // makes no sense for array storage
    }

    /**
     * Commits the transaction
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function commit()
    {
        // makes no sense for array storage
    }

    /**
     * Rolls back the transaction
     *
     * @return void
     */
    public function rollBack()
    {
        unset($this->messages[$this->lastMessageId]);
    }
}
