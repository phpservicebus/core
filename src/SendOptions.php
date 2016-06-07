<?php
namespace PSB\Core;


use PSB\Core\Util\Guard;

class SendOptions extends OutgoingOptions
{
    /**
     * @var string|null
     */
    private $destination;

    /**
     * @var bool
     */
    private $isRoutedToLocalInstance = false;

    /**
     * @param string $destination
     */
    public function setExplicitDestination($destination)
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

    public function routeToLocalEndpointInstance()
    {
        $this->isRoutedToLocalInstance = true;
    }

    /**
     * @return bool
     */
    public function isRoutedToLocalInstance()
    {
        return $this->isRoutedToLocalInstance;
    }
}
