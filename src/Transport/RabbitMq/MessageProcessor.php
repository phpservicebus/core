<?php
namespace PSB\Core\Transport\RabbitMq;


use PSB\Core\EndpointControlToken;
use PSB\Core\Exception\CriticalErrorException;
use PSB\Core\Transport\PushContext;
use PSB\Core\Transport\PushPipe;
use PSB\Core\Transport\ReceiveCancellationToken;

class MessageProcessor
{
    /**
     * @var BrokerModel
     */
    private $brokerModel;

    /**
     * @var RoutingTopology
     */
    private $routingTopology;

    /**
     * @var MessageConverter
     */
    private $messageConverter;

    /**
     * @param BrokerModel      $brokerModel
     * @param RoutingTopology  $routingTopology
     * @param MessageConverter $messageConverter
     */
    public function __construct(
        BrokerModel $brokerModel,
        RoutingTopology $routingTopology,
        MessageConverter $messageConverter
    ) {
        $this->brokerModel = $brokerModel;
        $this->routingTopology = $routingTopology;
        $this->messageConverter = $messageConverter;
    }

    /**
     * @param \AMQPEnvelope            $envelope
     * @param \AMQPQueue               $queue
     * @param PushPipe                 $pushPipe
     * @param string                   $errorQueue
     * @param ReceiveCancellationToken $cancellationToken
     * @param EndpointControlToken     $endpointControlToken
     *
     * @return bool
     */
    public function process(
        \AMQPEnvelope $envelope,
        \AMQPQueue $queue,
        PushPipe $pushPipe,
        $errorQueue,
        ReceiveCancellationToken $cancellationToken,
        EndpointControlToken $endpointControlToken
    ) {
        try {
            $messageId = '';
            $headers = [];
            $pushMessage = false;
            try {
                $messageId = $this->messageConverter->retrieveMessageId($envelope);
                $headers = $this->messageConverter->retrieveHeaders($envelope);
                $pushMessage = true;
            } catch (\Exception $e) {
                $this->routingTopology->sendToQueue(
                    $this->brokerModel,
                    $errorQueue,
                    $envelope->getBody(),
                    ['headers' => $envelope->getHeaders()]
                );
            }

            if ($pushMessage) {
                $pushPipe->push(
                    new PushContext(
                        $messageId,
                        $headers,
                        $envelope->getBody() ?: '',
                        $cancellationToken,
                        $endpointControlToken
                    )
                );
            }

            if ($cancellationToken->isCancellationRequested()) {
                $queue->reject($envelope->getDeliveryTag(), AMQP_REQUEUE);
            } else {
                $queue->ack($envelope->getDeliveryTag());
            }
        } catch (CriticalErrorException $e) {
            // just ... die
            throw $e;
        } catch (\Exception $e) {
            $queue->reject($envelope->getDeliveryTag(), AMQP_REQUEUE);
        }

        if ($endpointControlToken->isShutdownRequested()) {
            return false;
        }

        return true;
    }
}
