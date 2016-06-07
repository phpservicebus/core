<?php
namespace PSB\Core\Pipeline\Incoming\StageContext;


use PSB\Core\EndpointControlToken;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\OutgoingOptionsFactory;
use PSB\Core\Pipeline\BusOperations;
use PSB\Core\Pipeline\Incoming\IncomingContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Transport\IncomingPhysicalMessage;

class InvokeHandlerContext extends IncomingContext implements MessageHandlerContextInterface
{
    /**
     * @var MessageHandlerInterface
     */
    private $messageHandler;

    /**
     * @var object
     */
    private $messageBeingHandled;

    /**
     * @var bool
     */
    private $isHandlerInvocationAborted = false;

    /**
     * @param MessageHandlerInterface       $messageHandler
     * @param object                        $messageBeingHandled
     * @param string                        $messageId
     * @param array                         $headers
     * @param IncomingPhysicalMessage       $incomingPhysicalMessage
     * @param PendingTransportOperations    $pendingTransportOperations
     * @param BusOperations                 $busOperations
     * @param OutgoingOptionsFactory        $outgoingOptionsFactory
     * @param EndpointControlToken          $endpointControlToken
     * @param IncomingLogicalMessageContext $parentContext
     */
    public function __construct(
        MessageHandlerInterface $messageHandler,
        $messageBeingHandled,
        $messageId,
        array $headers,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $pendingTransportOperations,
        BusOperations $busOperations,
        OutgoingOptionsFactory $outgoingOptionsFactory,
        EndpointControlToken $endpointControlToken,
        IncomingLogicalMessageContext $parentContext
    ) {
        parent::__construct(
            $messageId,
            $headers,
            $incomingPhysicalMessage,
            $pendingTransportOperations,
            $busOperations,
            $outgoingOptionsFactory,
            $endpointControlToken,
            $parentContext
        );
        $this->messageHandler = $messageHandler;
        $this->messageBeingHandled = $messageBeingHandled;
    }

    public function doNotContinueDispatchingCurrentMessageToHandlers()
    {
        $this->isHandlerInvocationAborted = true;
    }

    /**
     * @return MessageHandlerInterface
     */
    public function getMessageHandler()
    {
        return $this->messageHandler;
    }

    /**
     * @return object
     */
    public function getMessageBeingHandled()
    {
        return $this->messageBeingHandled;
    }

    /**
     * @return boolean
     */
    public function isHandlerInvocationAborted()
    {
        return $this->isHandlerInvocationAborted;
    }
}
