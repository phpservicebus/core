<?php
namespace PSB\Core\Pipeline\Outgoing;


use PSB\Core\Pipeline\PipelineStageContext;

abstract class OutgoingContext extends PipelineStageContext
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
     * OutgoingContext constructor.
     *
     * @param string               $messageId
     * @param array                $headers
     * @param PipelineStageContext $parentContext
     */
    public function __construct($messageId, array $headers, PipelineStageContext $parentContext)
    {
        parent::__construct($parentContext);

        $this->messageId = $messageId;
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
}
