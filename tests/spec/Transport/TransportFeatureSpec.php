<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;

use PSB\Core\Transport\TransportFeature;

/**
 * @mixin TransportFeature
 */
class TransportFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\TransportFeature');
    }

    function it_describes_as_being_enabled_by_default()
    {
        $this->describe();
        $this->isEnabledByDefault()->shouldBe(true);
    }
}
