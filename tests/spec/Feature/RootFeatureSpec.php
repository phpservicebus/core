<?php

namespace spec\PSB\Core\Feature;

use PhpSpec\ObjectBehavior;

use PSB\Core\Feature\RootFeature;

/**
 * @mixin RootFeature
 */
class RootFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Feature\RootFeature');
    }

    function it_describes_as_being_enabled_by_default()
    {
        $this->describe();
        $this->isEnabledByDefault()->shouldReturn(true);
    }
}
