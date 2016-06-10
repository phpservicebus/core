<?php
namespace PSB\Core\Routing;


use PSB\Core\SendOptions;
use PSB\Core\Transport\Config\TransportInfrastructure;

class UnicastRouter implements UnicastRouterInterface
{
    /**
     * @var string
     */
    private $localAddress;

    /**
     * @var UnicastRoutingTable
     */
    private $unicastRoutingTable;

    /**
     * @var TransportInfrastructure
     */
    private $transportInfrastructure;

    /**
     * @param string                  $localAddress
     * @param UnicastRoutingTable     $unicastRoutingTable
     * @param TransportInfrastructure $transportInfrastructure
     */
    public function __construct(
        $localAddress,
        UnicastRoutingTable $unicastRoutingTable,
        TransportInfrastructure $transportInfrastructure
    ) {
        $this->localAddress = $localAddress;
        $this->unicastRoutingTable = $unicastRoutingTable;
        $this->transportInfrastructure = $transportInfrastructure;
    }

    /**
     * @param SendOptions $options
     * @param string      $messageClass
     *
     * @return AddressTagInterface[]
     */
    public function route(SendOptions $options, $messageClass)
    {
        $destination = $options->isRoutedToLocalInstance() ? $this->localAddress : null;
        $destination = $options->getExplicitDestination() ?: $destination;

        if ($destination === null || $destination === '') {
            $messageTypes = array_merge([$messageClass], array_values(class_implements($messageClass, true)));
            $endpointNames = $this->unicastRoutingTable->getEndpointNamesFor($messageTypes);
            $destinations = [];
            foreach ($endpointNames as $endpointName) {
                $destinations[] = $this->transportInfrastructure->toTransportAddress($endpointName);
            }
        } else {
            $destinations = [$destination];
        }

        $addressTags = [];
        foreach ($destinations as $destination) {
            $addressTags[] = new UnicastAddressTag($destination);
        }

        return $addressTags;
    }
}
