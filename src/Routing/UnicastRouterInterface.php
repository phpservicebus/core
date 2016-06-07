<?php
namespace PSB\Core\Routing;


use PSB\Core\SendOptions;

interface UnicastRouterInterface
{
    /**
     * @param SendOptions $options
     * @param string      $messageClass
     *
     * @return AddressTagInterface[]
     */
    public function route(SendOptions $options, $messageClass);
}
