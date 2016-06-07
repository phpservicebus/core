<?php
namespace PSB\Core\Outbox;


use PSB\Core\Util\Guard;

class OutboxTransportOperation
{
    /**
     * @var string
     */
    private $messageId;

    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param string $messageId
     * @param array  $options
     * @param string $body
     * @param array  $headers
     */
    public function __construct($messageId, array $options, $body, array $headers)
    {
        Guard::againstNullAndEmpty('messageId', $messageId);

        $this->messageId = $messageId;
        $this->options = $options;
        $this->body = $body;
        $this->headers = $headers;
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
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
