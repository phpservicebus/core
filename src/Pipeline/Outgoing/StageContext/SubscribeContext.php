<?php
namespace PSB\Core\Pipeline\Outgoing\StageContext;


use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\SubscribeOptions;
use PSB\Core\Util\Guard;

class SubscribeContext extends PipelineStageContext
{
    /**
     * @var string
     */
    private $eventFqcn;

    /**
     * @var SubscribeOptions
     */
    private $subscribeOptions;

    /**
     * @param string               $eventFqcn
     * @param SubscribeOptions     $options
     * @param PipelineStageContext $parentContext
     */
    public function __construct($eventFqcn, SubscribeOptions $options, PipelineStageContext $parentContext)
    {
        Guard::againstNullAndEmpty('eventFqcn', $eventFqcn);

        parent::__construct($parentContext);
        $this->eventFqcn = $eventFqcn;
        $this->subscribeOptions = $options;
    }

    /**
     * @return string
     */
    public function getEventFqcn()
    {
        return $this->eventFqcn;
    }

    /**
     * @return SubscribeOptions
     */
    public function getSubscribeOptions()
    {
        return $this->subscribeOptions;
    }
}
