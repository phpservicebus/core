<?php

namespace spec\PSB\Core\Transport\Config;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Transport\Config\TransportSubscriptionInfrastructure;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin TransportSubscriptionInfrastructure
 */
class TransportSubscriptionInfrastructureSpec extends ObjectBehavior
{
    /**
     * @var \specsupport\PSB\Core\SimpleCallable
     */
    private $subscriptionManagerFactoryMock;

    function let(SimpleCallable $subscriptionManagerFactory)
    {
        $this->subscriptionManagerFactoryMock = $subscriptionManagerFactory;

        $this->beConstructedWith($subscriptionManagerFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\Config\TransportSubscriptionInfrastructure');
    }

    function it_contains_the_subscription_manager_factory_set_at_construction()
    {
        $this->getSubscriptionManagerFactory()->shouldReturn($this->subscriptionManagerFactoryMock);
    }
}
