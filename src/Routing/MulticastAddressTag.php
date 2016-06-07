<?php
namespace PSB\Core\Routing;


class MulticastAddressTag implements AddressTagInterface
{
    /**
     * @var string
     */
    private $messageType;

    /**
     * @param string $messageType
     */
    public function __construct($messageType)
    {
        $this->messageType = $messageType;
    }

    /**
     * @return string
     */
    public function getMessageType()
    {
        return $this->messageType;
    }
}
