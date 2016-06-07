<?php
namespace PSB\Core\Pipeline\Incoming;


use PSB\Core\EndpointControlToken;
use PSB\Core\MessageProcessingContextInterface;
use PSB\Core\OutgoingOptionsFactory;
use PSB\Core\Pipeline\BusOperations;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineFactory;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\PublishOptions;
use PSB\Core\ReplyOptions;
use PSB\Core\SendOptions;
use PSB\Core\SubscribeOptions;
use PSB\Core\Transport\IncomingPhysicalMessage;
use PSB\Core\UnsubscribeOptions;

abstract class IncomingContext extends PipelineStageContext implements MessageProcessingContextInterface
{
    /**
     * @var string
     */
    protected $messageId;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var IncomingPhysicalMessage
     */
    protected $incomingPhysicalMessage;

    /**
     * @var PendingTransportOperations
     */
    protected $pendingTransportOperations;

    /**
     * @var BusOperations
     */
    protected $busOperations;

    /**
     * @var OutgoingOptionsFactory
     */
    protected $outgoingOptionsFactory;

    /**
     * @var PipelineFactory
     */
    protected $pipelineFactory;

    /**
     * @var EndpointControlToken
     */
    protected $endpointControlToken;

    /**
     * @param string                     $messageId
     * @param array                      $headers
     * @param IncomingPhysicalMessage    $incomingPhysicalMessage
     * @param PendingTransportOperations $pendingTransportOperations
     * @param BusOperations              $busOperations
     * @param OutgoingOptionsFactory     $outgoingOptionsFactory
     * @param EndpointControlToken       $endpointControlToken
     * @param PipelineStageContext       $parentContext
     */
    public function __construct(
        $messageId,
        array $headers,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $pendingTransportOperations,
        BusOperations $busOperations,
        OutgoingOptionsFactory $outgoingOptionsFactory,
        EndpointControlToken $endpointControlToken,
        PipelineStageContext $parentContext
    ) {
        parent::__construct($parentContext);

        $this->messageId = $messageId;
        $this->headers = $headers;
        $this->incomingPhysicalMessage = $incomingPhysicalMessage;
        $this->pendingTransportOperations = $pendingTransportOperations;
        $this->busOperations = $busOperations;
        $this->outgoingOptionsFactory = $outgoingOptionsFactory;
        $this->endpointControlToken = $endpointControlToken;
    }

    /**
     * @param object           $message
     * @param SendOptions|null $options
     */
    public function send($message, SendOptions $options = null)
    {
        $options = $options ?: $this->outgoingOptionsFactory->createSendOptions();

        $this->busOperations->send($message, $options, $this);
    }

    /**
     * @param object           $message
     * @param SendOptions|null $options
     */
    public function sendLocal($message, SendOptions $options = null)
    {
        $options = $options ?: $this->outgoingOptionsFactory->createSendOptions();
        $options->routeToLocalEndpointInstance();

        $this->send($message, $options);
    }

    /**
     * @param object              $message
     * @param PublishOptions|null $options
     */
    public function publish($message, PublishOptions $options = null)
    {
        $options = $options ?: $this->outgoingOptionsFactory->createPublishOptions();

        $this->busOperations->publish($message, $options, $this);
    }

    /**
     * @param object                $message
     * @param SubscribeOptions|null $options
     */
    public function subscribe($message, SubscribeOptions $options = null)
    {
        $options = $options ?: $this->outgoingOptionsFactory->createSubscribeOptions();

        $this->busOperations->subscribe($message, $options, $this);
    }

    /**
     * @param object                  $message
     * @param UnsubscribeOptions|null $options
     */
    public function unsubscribe($message, UnsubscribeOptions $options = null)
    {
        $options = $options ?: $this->outgoingOptionsFactory->createUnsubscribeOptions();

        $this->busOperations->unsubscribe($message, $options, $this);
    }

    /**
     * @param object            $message
     * @param ReplyOptions|null $options
     */
    public function reply($message, ReplyOptions $options = null)
    {
        $options = $options ?: $this->outgoingOptionsFactory->createReplyOptions();

        $this->busOperations->reply($message, $options, $this);
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * @param array $headers
     */
    public function replaceHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return IncomingPhysicalMessage
     */
    public function getIncomingPhysicalMessage()
    {
        return $this->incomingPhysicalMessage;
    }

    /**
     * @return PendingTransportOperations
     */
    public function getPendingTransportOperations()
    {
        return $this->pendingTransportOperations;
    }

    /**
     * @param string $address
     *
     * @codeCoverageIgnore
     */
    public function forwardCurrentMessageTo($address)
    {
        // TODO: Implement forwardCurrentMessageTo() method.
    }

    /**
     * It requests an endpoint shutdown to take place after the current attempt of processing this message completes,
     * regardless of the way it completes (with success or failure/exception).
     *
     * It effectively pulls out of listening for more messages on the queue.
     */
    public function shutdownThisEndpointAfterCurrentMessage()
    {
        $this->endpointControlToken->requestShutdown();
    }

    /**
     * @return EndpointControlToken
     */
    public function getEndpointControlToken()
    {
        return $this->endpointControlToken;
    }
}
