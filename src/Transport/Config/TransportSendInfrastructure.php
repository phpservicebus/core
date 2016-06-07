<?php
namespace PSB\Core\Transport\Config;


class TransportSendInfrastructure
{
    /**
     * @var callable
     */
    private $messageDispatcherFactory;

    /**
     * @param callable $messageDispatcherFactory
     */
    public function __construct(callable $messageDispatcherFactory)
    {
        $this->messageDispatcherFactory = $messageDispatcherFactory;
    }

    /**
     * @return callable
     */
    public function getMessageDispatcherFactory()
    {
        return $this->messageDispatcherFactory;
    }
}
