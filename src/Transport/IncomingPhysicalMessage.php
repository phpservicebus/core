<?php
namespace PSB\Core\Transport;


use PSB\Core\HeaderTypeEnum;
use PSB\Core\Util\Guard;

class IncomingPhysicalMessage
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
    private $originalBody;

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
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string|null
     */
    public function getReplyToAddress()
    {
        return isset($this->headers[HeaderTypeEnum::REPLY_TO_ADDRESS]) ? $this->headers[HeaderTypeEnum::REPLY_TO_ADDRESS] : null;
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
     * @param string $newBody
     */
    public function replaceBody($newBody)
    {
        Guard::againstNull('body', $newBody);

        if ($this->originalBody === null) {
            $this->originalBody = $this->body;
        }

        $this->body = $newBody;
    }

    /**
     * Makes sure that the body is reset to the original state as it was when the message was created.
     */
    public function revertToOriginalBodyIfNeeded()
    {
        if ($this->originalBody !== null) {
            $this->body = $this->originalBody;
        }
    }
}
