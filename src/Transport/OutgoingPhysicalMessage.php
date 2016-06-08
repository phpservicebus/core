<?php
namespace PSB\Core\Transport;


use PSB\Core\Util\Guard;

class OutgoingPhysicalMessage
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
     * @param string $messageId
     * @param array  $headers
     * @param string $body
     */
    public function __construct($messageId, array $headers, $body)
    {
        Guard::againstNullAndEmpty('messageId', $messageId);
        Guard::againstNull('body', $body);

        $this->messageId = $messageId;
        $this->headers = $headers;
        $this->body = $body;
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
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function replaceBody($body)
    {
        Guard::againstNull('body', $body);

        $this->body = $body;
    }
}
