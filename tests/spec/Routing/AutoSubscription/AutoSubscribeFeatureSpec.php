<?php

namespace spec\PSB\Core\Routing\AutoSubscription;

use PhpSpec\ObjectBehavior;

use PSB\Core\Routing\AutoSubscription\AutoSubscribeFeature;

/**
 * @mixin AutoSubscribeFeature
 */
class AutoSubscribeFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Routing\AutoSubscription\AutoSubscribeFeature');
    }

    function it_describes_as_being_enabled_by_default()
    {
        $this->describe();
        $this->isEnabledByDefault()->shouldBe(true);
    }
}
