<?php
namespace PSB\Core\Routing\AutoSubscription;


use PSB\Core\BusContextInterface;
use PSB\Core\Feature\FeatureStartupTaskInterface;

class SubscriptionApplierStartupTask implements FeatureStartupTaskInterface
{
    /**
     * @var string[]
     */
    private $messageFqcnsHandledByThisEndpoint;

    /**
     * @param string[] $messageFqcnsHandledByThisEndpoint
     */
    public function __construct(array $messageFqcnsHandledByThisEndpoint)
    {
        $this->messageFqcnsHandledByThisEndpoint = $messageFqcnsHandledByThisEndpoint;
    }

    /**
     * @param BusContextInterface $busContext
     */
    public function start(BusContextInterface $busContext)
    {
        foreach ($this->messageFqcnsHandledByThisEndpoint as $messageFqcn) {
            $busContext->subscribe($messageFqcn);
        }
    }
}
