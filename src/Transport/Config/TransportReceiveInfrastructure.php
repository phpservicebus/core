<?php
namespace PSB\Core\Transport\Config;


class TransportReceiveInfrastructure
{
    /**
     * @var callable
     */
    private $messagePusherFactory;

    /**
     * @var callable
     */
    private $queueCreatorFactory;

    /**
     * @param callable $messagePusherFactory
     * @param callable  $queueCreatorFactory
     */
    public function __construct(
        callable $messagePusherFactory,
        callable $queueCreatorFactory
    ) {
        $this->messagePusherFactory = $messagePusherFactory;
        $this->queueCreatorFactory = $queueCreatorFactory;
    }

    /**
     * @return callable
     */
    public function getMessagePusherFactory()
    {
        return $this->messagePusherFactory;
    }

    /**
     * @return callable
     */
    public function getQueueCreatorFactory()
    {
        return $this->queueCreatorFactory;
    }
}
