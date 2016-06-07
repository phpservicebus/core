<?php
namespace PSB\Core;


abstract class OutgoingOptions
{
    /**
     * @var bool
     */
    private $immediateDispatch = false;

    /**
     * @var string
     */
    private $messageId;

    /**
     * @var array
     */
    private $outgoingHeaders = [];

    /**
     * @return $this
     */
    public function requireImmediateDispatch()
    {
        $this->immediateDispatch = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isImmediateDispatchEnabled()
    {
        return $this->immediateDispatch;
    }

    /**
     * @param string $messageId
     *
     * @return $this
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @param array $headers
     */
    public function setOutgoingHeaders(array $headers)
    {
        $this->outgoingHeaders = $headers;
    }

    /**
     * @return array
     */
    public function getOutgoingHeaders()
    {
        return $this->outgoingHeaders;
    }
}
