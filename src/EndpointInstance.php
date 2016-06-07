<?php
namespace PSB\Core;


class EndpointInstance implements EndpointInstanceInterface
{
    /**
     * @var BusContextInterface
     */
    private $busContext;

    /**
     * @param BusContextInterface $busContext
     */
    public function __construct(BusContextInterface $busContext)
    {
        $this->busContext = $busContext;
    }

    /**
     * @param object           $message
     * @param SendOptions|null $options
     */
    public function send($message, SendOptions $options = null)
    {
        $this->busContext->send($message, $options);
    }

    /**
     * @param object           $message
     * @param SendOptions|null $options
     */
    public function sendLocal($message, SendOptions $options = null)
    {
        $this->busContext->sendLocal($message, $options);
    }

    /**
     * @param object              $message
     * @param PublishOptions|null $options
     */
    public function publish($message, PublishOptions $options = null)
    {
        $this->busContext->publish($message, $options);
    }

    /**
     * @param string                $eventFqcn
     * @param SubscribeOptions|null $options
     */
    public function subscribe($eventFqcn, SubscribeOptions $options = null)
    {
        $this->busContext->subscribe($eventFqcn, $options);
    }

    /**
     * @param string                  $eventFqcn
     * @param UnsubscribeOptions|null $options
     */
    public function unsubscribe($eventFqcn, UnsubscribeOptions $options = null)
    {
        $this->busContext->unsubscribe($eventFqcn, $options);
    }
}
