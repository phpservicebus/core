<?php
namespace PSB\Core\Transport;


use PSB\Core\EndpointControlToken;
use PSB\Core\Util\Guard;

class PushContext
{
    /**
     * @var string
     */
    private $messageId;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $body;

    /**
     * @var ReceiveCancellationToken
     */
    private $cancellationToken;

    /**
     * @var EndpointControlToken
     */
    private $endpointControlToken;

    /**
     * @param string                   $messageId
     * @param array                    $headers
     * @param string                   $body
     * @param ReceiveCancellationToken $cancellationToken
     * @param EndpointControlToken     $endpointControlToken
     */
    public function __construct(
        $messageId,
        array $headers,
        $body,
        ReceiveCancellationToken $cancellationToken,
        EndpointControlToken $endpointControlToken
    ) {
        Guard::againstNullAndEmpty('messageId', $messageId);
        Guard::againstNullAndEmpty('headers', $headers);
        Guard::againstNull('body', $body);

        $this->messageId = $messageId;
        $this->headers = $headers;
        $this->body = $body;
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
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return ReceiveCancellationToken
     */
    public function getCancellationToken()
    {
        return $this->cancellationToken;
    }

    /**
     * @return EndpointControlToken
     */
    public function getEndpointControlToken()
    {
        return $this->endpointControlToken;
    }
}
