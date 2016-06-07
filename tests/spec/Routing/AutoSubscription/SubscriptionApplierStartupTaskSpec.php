<?php

namespace spec\PSB\Core\Routing\AutoSubscription;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\BusContextInterface;
use PSB\Core\Routing\AutoSubscription\SubscriptionApplierStartupTask;

/**
 * @mixin SubscriptionApplierStartupTask
 */
class SubscriptionApplierStartupTaskSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType('PSB\Core\Routing\AutoSubscription\SubscriptionApplierStartupTask');
    }

    function it_makes_no_subscriptions_if_there_are_no_messages(BusContextInterface $busContext)
    {
        $this->beConstructedWith([]);

        $busContext->subscribe(Argument::any())->shouldNotBeCalled();

        $this->start($busContext);
    }

    function it_subscribes_for_all_messages(BusContextInterface $busContext)
    {
        $this->beConstructedWith(['class1', 'class2']);

        $busContext->subscribe('class1')->shouldBeCalled();
        $busContext->subscribe('class2')->shouldBeCalled();

        $this->start($busContext);
    }
}
