<?php
namespace PSB\Core\Transport;


interface TransportConnectionFactoryInterface
{
    /**
     * @return mixed
     */
    public function createConnection();
}
