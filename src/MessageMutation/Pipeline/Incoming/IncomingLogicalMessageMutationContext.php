<?php
namespace PSB\Core\MessageMutation\Pipeline\Incoming;


use PSB\Core\Util\Guard;

class IncomingLogicalMessageMutationContext
{
    /**
     * @var object
     */
    private $message;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var bool
     */
    private $hasMessageChanged = false;

    /**
     * @param object $message
     * @param array  $headers
     */
    public function __construct($message, array $headers)
    {
        Guard::againstNull('message', $message);
        Guard::againstNonObject('message', $message);

        $this->message = $message;
        $this->headers = $headers;
    }

    /**
     * @return object
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return bool
     */
    public function hasMessageChanged()
    {
        return $this->hasMessageChanged;
    }

    /**
     * @param object $message
     */
    public function updateMessage($message)
    {
        Guard::againstNull('message', $message);
        Guard::againstNonObject('message', $message);

        $this->message = $message;
        $this->hasMessageChanged = true;
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
