<?php
namespace PSB\Core\Transport;


interface QueueCreatorInterface
{
    /**
     * Creates message queues for the defined queue bindings.
     *
     * @param QueueBindings $queueBindings
     */
    public function createIfNecessary(QueueBindings $queueBindings);
}
