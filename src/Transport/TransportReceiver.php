<?php
namespace PSB\Core\Transport;


class TransportReceiver
{
    /**
     * @var MessagePusherInterface
     */
    private $messagePusher;

    /**
     * @var PushSettings
     */
    private $pushSettings;

    /**
     * @var PushPipe
     */
    private $pushPipe;

    /**
     * @param MessagePusherInterface $messagePusher
     * @param PushSettings           $pushSettings
     * @param PushPipe               $pushPipe
     */
    public function __construct(
        MessagePusherInterface $messagePusher,
        PushSettings $pushSettings,
        PushPipe $pushPipe
    ) {
        $this->messagePusher = $messagePusher;
        $this->pushSettings = $pushSettings;
        $this->pushPipe = $pushPipe;
    }

    public function start()
    {
        $this->messagePusher->init($this->pushPipe, $this->pushSettings);
        $this->messagePusher->start();
    }
}
