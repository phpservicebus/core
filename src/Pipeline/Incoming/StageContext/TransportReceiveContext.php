<?php
namespace PSB\Core\Pipeline\Incoming\StageContext;


use PSB\Core\EndpointControlToken;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\Transport\IncomingPhysicalMessage;
use PSB\Core\Transport\ReceiveCancellationToken;

class TransportReceiveContext extends PipelineStageContext
{
    /**
     * @var string
     */
    private $messageId;

    /**
     * @var IncomingPhysicalMessage
     */
    private $message;

    /**
     * @var ReceiveCancellationToken
     */
    private $cancellationToken;

    /**
     * @var EndpointControlToken
     */
    private $endpointControlToken;

    /**
     * @param string                   $incomingMessageId
     * @param IncomingPhysicalMessage  $incomingMessage
     * @param ReceiveCancellationToken $cancellationToken
     * @param EndpointControlToken     $endpointControlToken
     * @param PipelineStageContext     $parent
     */
    public function __construct(
        $incomingMessageId,
        IncomingPhysicalMessage $incomingMessage,
        ReceiveCancellationToken $cancellationToken,
        EndpointControlToken $endpointControlToken,
        PipelineStageContext $parent
    ) {
        parent::__construct($parent);

        $this->messageId = $incomingMessageId;
        $this->message = $incomingMessage;
        $this->cancellationToken = $cancellationToken;
        $this->endpointControlToken = $endpointControlToken;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return IncomingPhysicalMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function abortReceiveOperation()
    {
        $this->cancellationToken->cancel();
    }

    /**
     * @return EndpointControlToken
     */
    public function getEndpointControlToken()
    {
        return $this->endpointControlToken;
    }
}
