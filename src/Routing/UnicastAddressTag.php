<?php
namespace PSB\Core\Routing;


class UnicastAddressTag implements AddressTagInterface
{
    /**
     * @var string
     */
    private $destination;

    /**
     * @param string $destination
     */
    public function __construct($destination)
    {
        $this->destination = $destination;
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }
}
