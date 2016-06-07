<?php
namespace PSB\Core;


interface MessageHandlerInterface
{
    public function handle($message, MessageHandlerContextInterface $context);
}
