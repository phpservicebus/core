<?php
namespace PSB\Core\Transport;


use PSB\Core\Routing\AddressTagInterface;

class TransportOperation
{
    /**
     * @var OutgoingPhysicalMessage
     */
    private $message;

    /**
     * @var string
     */
    private $addressTag;

    /**
     * @param OutgoingPhysicalMessage $message
     * @param AddressTagInterface     $addressTag
     */
    public function __construct(OutgoingPhysicalMessage $message, AddressTagInterface $addressTag)
    {
        $this->message = $message;
        $this->addressTag = $addressTag;
    }

    /**
     * @return OutgoingPhysicalMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return AddressTagInterface
     */
    public function getAddressTag()
    {
        return $this->addressTag;
    }
}
