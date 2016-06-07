<?php
namespace PSB\Core;


interface BusContextInterface
{
    /**
     * @param object           $message
     * @param SendOptions|null $options
     */
    public function send($message, SendOptions $options = null);

    /**
     * @param object           $message
     * @param SendOptions|null $options
     */
    public function sendLocal($message, SendOptions $options = null);

    /**
     * @param object              $message
     * @param PublishOptions|null $options
     */
    public function publish($message, PublishOptions $options = null);

    /**
     * @param string                $eventFqcn
     * @param SubscribeOptions|null $options
     */
    public function subscribe($eventFqcn, SubscribeOptions $options = null);

    /**
     * @param string                  $eventFqcn
     * @param UnsubscribeOptions|null $options
     */
    public function unsubscribe($eventFqcn, UnsubscribeOptions $options = null);
}
