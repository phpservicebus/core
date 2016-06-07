<?php
namespace PSB\Core\Outbox;

interface OutboxStorageInterface
{
    /**
     * Fetches the given message from the storage. It returns null if no message is found.
     *
     * @param string $messageId
     *
     * @return OutboxMessage|null
     */
    public function get($messageId);

    /**
     * Stores the message to enable deduplication and re-dispatching of transport operations.
     * Throws an exception if a message with the same ID already exists.
     *
     * @param OutboxMessage $message
     *
     * @return void
     */
    public function store(OutboxMessage $message);

    /**
     * @param string $messageId
     *
     * @return void
     */
    public function markAsDispatched($messageId);

    /**
     * Initiates the transaction
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * Commits the transaction
     *
     * @return void
     */
    public function commit();

    /**
     * Rolls back the transaction
     *
     * @return void
     */
    public function rollBack();
}
