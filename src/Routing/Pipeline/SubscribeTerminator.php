<?php
namespace PSB\Core\Routing\Pipeline;


use PSB\Core\Pipeline\Outgoing\StageContext\SubscribeContext;
use PSB\Core\Pipeline\PipelineTerminator;
use PSB\Core\Transport\SubscriptionManagerInterface;

class SubscribeTerminator extends PipelineTerminator
{
    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * @param SubscriptionManagerInterface $subscriptionManager
     */
    public function __construct(SubscriptionManagerInterface $subscriptionManager)
    {
        $this->subscriptionManager = $subscriptionManager;
    }

    /**
     * @param SubscribeContext $context
     */
    protected function terminate($context)
    {
        $this->subscriptionManager->subscribe($context->getEventFqcn());
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return SubscribeContext::class;
    }

    /**
     * @return string
     */
    public static function getNextStageContextClass()
    {
        return '';
    }
}
