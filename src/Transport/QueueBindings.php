<?php
namespace PSB\Core\Transport;

/**
 * Contains information about the queues this endpoint is using.
 */
class QueueBindings
{
    /**
     * @var array
     */
    private $receivingAddresses = [];

    /**
     * @var array
     */
    private $sendingAddresses = [];

    /**
     * @param string $address
     */
    public function bindReceiving($address)
    {
        $this->receivingAddresses[] = $address;
    }

    /**
     * @param string $address
     */
    public function bindSending($address)
    {
        $this->sendingAddresses[] = $address;
    }

    /**
     * @return array
     */
    public function getReceivingAddresses()
    {
        return $this->receivingAddresses;
    }

    /**
     * @return array
     */
    public function getSendingAddresses()
    {
        return $this->sendingAddresses;
    }
}
