<?php
namespace PSB\Core;


use PSB\Core\Transport\IncomingPhysicalMessage;

interface MessageProcessingContextInterface extends BusContextInterface
{
    /**
     * @return string
     */
    public function getMessageId();

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @return IncomingPhysicalMessage
     */
    public function getIncomingPhysicalMessage();

    /**
     * @param object            $message
     * @param ReplyOptions|null $options
     */
    public function reply($message, ReplyOptions $options = null);

    /**
     * @param string $address
     */
    public function forwardCurrentMessageTo($address);

    public function shutdownThisEndpointAfterCurrentMessage();
}
