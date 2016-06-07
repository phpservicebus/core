<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Transport\ReceiveCancellationToken;

/**
 * @mixin ReceiveCancellationToken
 */
class ReceiveCancellationTokenSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\ReceiveCancellationToken');
    }

    function it_can_be_cancelled()
    {
        $this->cancel();

        $this->isCancellationRequested()->shouldReturn(true);
    }

    function it_can_be_left_untouched()
    {
        $this->isCancellationRequested()->shouldReturn(false);
    }
}
