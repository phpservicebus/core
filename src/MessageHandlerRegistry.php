<?php
namespace PSB\Core;


class MessageHandlerRegistry
{
    const EVENT_HANDLER = 0;
    const COMMAND_HANDLER = 1;

    /**
     * @var string[][]
     */
    private $handlers = [self::EVENT_HANDLER => [], self::COMMAND_HANDLER => []];

    /**
     * @param string $eventFqcn
     * @param string $handlerContainerId
     */
    public function registerEventHandler($eventFqcn, $handlerContainerId)
    {
        $this->registerMessageHandler($eventFqcn, $handlerContainerId, self::EVENT_HANDLER);
    }

    /**
     * @param string $eventFqcn
     * @param string $handlerContainerId
     */
    public function registerCommandHandler($eventFqcn, $handlerContainerId)
    {
        $this->registerMessageHandler($eventFqcn, $handlerContainerId, self::COMMAND_HANDLER);
    }

    /**
     * @param string $messageFqcn
     * @param string $handlerContainerId
     * @param int    $messageType
     */
    private function registerMessageHandler($messageFqcn, $handlerContainerId, $messageType)
    {
        if (!isset($this->handlers[$messageType][$messageFqcn])) {
            $this->handlers[$messageType][$messageFqcn] = [];
        }

        if (in_array($handlerContainerId, $this->handlers[$messageType][$messageFqcn])) {
            return;
        }

        $this->handlers[$messageType][$messageFqcn][] = $handlerContainerId;
    }

    /**
     * @param array $messageFqcns
     *
     * @return string[]
     */
    public function getHandlerIdsFor(array $messageFqcns)
    {
        $messageFqcns = array_flip($messageFqcns);
        $handlerIds = [];
        foreach ($this->handlers as $messageToHandlerIds) {
            $filteredMessageToHandlerIds = array_intersect_key($messageToHandlerIds, $messageFqcns);
            $handlerIds = array_merge(
                $handlerIds,
                iterator_to_array(
                    new \RecursiveIteratorIterator(new \RecursiveArrayIterator($filteredMessageToHandlerIds))
                )
            );
        }
        return array_unique($handlerIds);
    }

    /**
     * @return array
     */
    public function getEventFqcns()
    {
        return array_keys($this->handlers[self::EVENT_HANDLER]);
    }

    /**
     * @return array
     */
    public function getCommandFqcns()
    {
        return array_keys($this->handlers[self::COMMAND_HANDLER]);
    }

    /**
     * @return array
     */
    public function getMessageFqcns()
    {
        return array_merge($this->getEventFqcns(), $this->getCommandFqcns());
    }
}
