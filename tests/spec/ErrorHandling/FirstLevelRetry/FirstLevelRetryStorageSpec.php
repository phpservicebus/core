<?php

namespace spec\PSB\Core\ErrorHandling\FirstLevelRetry;

use PhpSpec\ObjectBehavior;

use PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryStorage;

/**
 * @mixin FirstLevelRetryStorage
 */
class FirstLevelRetryStorageSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryStorage');
    }

    function it_increments_failures_for_message()
    {
        $this->incrementFailuresForMessage('someid');
        $this->incrementFailuresForMessage('someid');
        $this->getFailuresForMessage('someid')->shouldReturn(2);
    }

    function it_returns_zero_when_getting_retries_if_message_is_unknown()
    {
        $this->getFailuresForMessage('someid')->shouldReturn(0);
    }

    function it_clears_failures_for_a_message()
    {
        $this->incrementFailuresForMessage('someid1');
        $this->incrementFailuresForMessage('someid1');
        $this->incrementFailuresForMessage('someid2');
        $this->clearFailuresForMessage('someid1');
        $this->getFailuresForMessage('someid1')->shouldReturn(0);
        $this->getFailuresForMessage('someid2')->shouldReturn(1);
    }

    function it_clears_failures_for_all_messages()
    {
        $this->incrementFailuresForMessage('someid1');
        $this->incrementFailuresForMessage('someid1');
        $this->incrementFailuresForMessage('someid2');
        $this->clearAllFailures();
        $this->getFailuresForMessage('someid1')->shouldReturn(0);
        $this->getFailuresForMessage('someid2')->shouldReturn(0);
    }
}
