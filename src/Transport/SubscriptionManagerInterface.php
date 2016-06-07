<?php
namespace PSB\Core\Transport;


interface SubscriptionManagerInterface
{
    /**
     * @param string $eventFqcn
     */
    public function subscribe($eventFqcn);

    /**
     * @param string $eventFqcn
     */
    public function unsubscribe($eventFqcn);
}
