<?php
namespace PSB\Core\Transport\RabbitMq;


use PSB\Core\EndpointControlToken;
use PSB\Core\Transport\MessagePusherInterface;
use PSB\Core\Transport\PushPipe;
use PSB\Core\Transport\PushSettings;
use PSB\Core\Transport\ReceiveCancellationToken;

class RabbitMqMessagePusher implements MessagePusherInterface
{
    /**
     * @var BrokerModel
     */
    private $brokerModel;

    /**
     * @var MessageProcessor
     */
    private $messageProcessor;

    /**
     * @var PushPipe
     */
    private $pushPipe;

    /**
     * @var PushSettings
     */
    private $pushSettings;

    /**
     * @param BrokerModel      $brokerModel
     * @param MessageProcessor $messageProcessor
     */
    public function __construct(
        BrokerModel $brokerModel,
        MessageProcessor $messageProcessor
    ) {
        $this->brokerModel = $brokerModel;
        $this->messageProcessor = $messageProcessor;
    }

    /**
     * It initializes the message pusher with the pipe it will send messages through.
     * The pipe is invoked for each incoming message.
     *
     * @param PushPipe     $pushPipe
     * @param PushSettings $pushSettings
     */
    public function init(PushPipe $pushPipe, PushSettings $pushSettings)
    {
        $this->pushPipe = $pushPipe;
        $this->pushSettings = $pushSettings;

        if ($pushSettings->isPurgeOnStartup()) {
            $this->brokerModel->purgeQueue($pushSettings->getInputQueue());
        }
    }

    /**
     * Starts pushing messages
     */
    public function start()
    {
        $this->brokerModel->consume(
            $this->pushSettings->getInputQueue(),
            function (\AMQPEnvelope $envelope, \AMQPQueue $queue) {
                return $this->messageProcessor->process(
                    $envelope,
                    $queue,
                    $this->pushPipe,
                    $this->pushSettings->getErrorQueue(),
                    new ReceiveCancellationToken(),
                    new EndpointControlToken()
                );
            }
        );
    }
}
