<?php
namespace PSB\Core\Transport;


class ReceiveCancellationToken
{
    private $isCancelRequested = false;

    public function cancel()
    {
        $this->isCancelRequested = true;
    }

    public function isCancellationRequested()
    {
        return $this->isCancelRequested;
    }
}
