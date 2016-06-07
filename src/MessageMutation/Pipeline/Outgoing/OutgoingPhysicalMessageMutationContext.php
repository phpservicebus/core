<?php
namespace PSB\Core\MessageMutation\Pipeline\Outgoing;


use PSB\Core\Util\Guard;

class OutgoingPhysicalMessageMutationContext
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $headers;

    /**
     * @param string $body
     * @param array  $headers
     */
    public function __construct($body, array $headers)
    {
        Guard::againstNull('body', $body);

        $this->body = $body;
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
     * @param string $newBody
     */
    public function replaceBody($newBody)
    {
        Guard::againstNull('body', $newBody);

        $this->body = $newBody;
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
}
