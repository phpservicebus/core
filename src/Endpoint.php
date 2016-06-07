<?php
namespace PSB\Core;


class Endpoint
{
    /**
     * @param EndpointConfigurator $configurator
     *
     * @return StartableEndpoint
     */
    public static function build(EndpointConfigurator $configurator)
    {
        return $configurator->build();
    }

    /**
     * @param EndpointConfigurator $configurator
     *
     * @return StartableEndpoint
     */
    public static function prepare(EndpointConfigurator $configurator)
    {
        return self::build($configurator)->prepare();
    }

    /**
     * @param EndpointConfigurator $configurator
     *
     * @return EndpointInstance
     */
    public static function start(EndpointConfigurator $configurator)
    {
        return self::prepare($configurator)->start();
    }
}
