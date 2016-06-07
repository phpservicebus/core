<?php
namespace PSB\Core;


use PSB\Core\Util\Guard;

class ReplyOptions extends OutgoingOptions
{
    /**
     * @var string
     */
    private $destination;

    /**
     * @param string $destination
     */
    public function overrideReplyToAddressOfIncomingMessage($destination)
    {
        Guard::againstNullAndEmpty('destination', $destination);

        $this->destination = $destination;
    }

    /**
     * @return string|null
     */
    public function getExplicitDestination()
    {
        return $this->destination;
    }
}
