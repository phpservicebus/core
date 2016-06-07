<?php
namespace PSB\Core;


class EndpointControlToken
{
    /**
     * @var bool
     */
    private $isShutdownRequested = false;

    public function requestShutdown()
    {
        $this->isShutdownRequested = true;
    }

    /**
     * @return bool
     */
    public function isShutdownRequested()
    {
        return $this->isShutdownRequested;
    }
}
