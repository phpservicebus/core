<?php
namespace PSB\Core;


interface MessageHandlerContextInterface extends MessageProcessingContextInterface
{
    public function doNotContinueDispatchingCurrentMessageToHandlers();
}
