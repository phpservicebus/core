<?php
namespace PSB\Core\Transport;


interface MessageDispatcherInterface
{
    /**
     * @param TransportOperations $transportOperations
     */
    public function dispatch(TransportOperations $transportOperations);
}
