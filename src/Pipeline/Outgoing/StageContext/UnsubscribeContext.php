<?php
namespace PSB\Core\Pipeline\Outgoing\StageContext;


use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\UnsubscribeOptions;
use PSB\Core\Util\Guard;

class UnsubscribeContext extends PipelineStageContext
{
    /**
     * @var string
     */
    private $eventFqcn;

    /**
     * @var UnsubscribeOptions
     */
    private $unsubscribeOptions;

    /**
     * @param string               $eventFqcn
     * @param UnsubscribeOptions   $options
     * @param PipelineStageContext $parentContext
     */
    public function __construct($eventFqcn, UnsubscribeOptions $options, PipelineStageContext $parentContext)
    {
        Guard::againstNullAndEmpty('eventFqcn', $eventFqcn);

        parent::__construct($parentContext);
        $this->eventFqcn = $eventFqcn;
        $this->unsubscribeOptions = $options;
    }

    /**
     * @return string
     */
    public function getEventFqcn()
    {
        return $this->eventFqcn;
    }

    /**
     * @return UnsubscribeOptions
     */
    public function getUnsubscribeOptions()
    {
        return $this->unsubscribeOptions;
    }
}
