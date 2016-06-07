<?php
namespace PSB\Core\Transport;

/**
 * Allows the transport to push messages to the core.
 */
interface MessagePusherInterface
{
    /**
     * It initializes the message pusher with the pipe it will send messages through.
     * The pipe is invoked for each incoming message.
     *
     * @param PushPipe     $pushPipe
     * @param PushSettings $pushSettings
     */
    public function init(PushPipe $pushPipe, PushSettings $pushSettings);

    /**
     * Starts pushing messages
     */
    public function start();
}
