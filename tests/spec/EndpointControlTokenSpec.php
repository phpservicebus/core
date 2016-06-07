<?php

namespace spec\PSB\Core;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\EndpointControlToken;

/**
 * @mixin EndpointControlToken
 */
class EndpointControlTokenSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\EndpointControlToken');
    }

    function it_can_request_endpoint_shutdown()
    {
        $this->isShutdownRequested()->shouldBe(false);
        $this->requestShutdown();
        $this->isShutdownRequested()->shouldBe(true);
    }
}
