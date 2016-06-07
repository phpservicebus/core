<?php

namespace spec\PSB\Core\ErrorHandling\FirstLevelRetry;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryPolicy;

/**
 * @mixin FirstLevelRetryPolicy
 */
class FirstLevelRetryPolicySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(0);
        $this->shouldHaveType('PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryPolicy');
    }

    function it_should_give_up_if_retries_bigger_than_max_retries()
    {
        $this->beConstructedWith(5);
        $this->callOnWrappedObject('shouldGiveUp', [6])->shouldReturn(true);
        $this->callOnWrappedObject('shouldGiveUp', [5])->shouldReturn(true);
    }

    function it_should_not_give_up_if_retries_lower_than_max_retries()
    {
        $this->beConstructedWith(5);
        $this->callOnWrappedObject('shouldGiveUp', [4])->shouldReturn(false);
        $this->callOnWrappedObject('shouldGiveUp', [0])->shouldReturn(false);
    }
}
