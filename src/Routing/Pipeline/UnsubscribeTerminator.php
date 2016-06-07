<?php
namespace PSB\Core\Routing\Pipeline;


use PSB\Core\Pipeline\Outgoing\StageContext\UnsubscribeContext;
use PSB\Core\Pipeline\PipelineTerminator;
use PSB\Core\Transport\SubscriptionManagerInterface;

class UnsubscribeTerminator extends PipelineTerminator
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
     * @param UnsubscribeContext $context
     */
    protected function terminate($context)
    {
        $this->subscriptionManager->unsubscribe($context->getEventFqcn());
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return UnsubscribeContext::class;
    }

    /**
     * @return string
     */
    public static function getNextStageContextClass()
    {
        return '';
    }
}
