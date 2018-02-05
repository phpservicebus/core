<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;

use PSB\Core\Transport\PushSettings;

/**
 * @mixin PushSettings
 */
class PushSettingsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('input', 'error', false);
        $this->shouldHaveType('PSB\Core\Transport\PushSettings');
    }

    function it_contains_the_input_queue_set_on_construction()
    {
        $this->beConstructedWith('input', 'error', false);
        $this->getInputQueue()->shouldReturn('input');
    }

    function it_contains_the_error_queue_set_on_construction()
    {
        $this->beConstructedWith('input', 'error', false);
        $this->getErrorQueue()->shouldReturn('error');
    }

    function it_contains_the_purge_option_set_on_construction()
    {
        $this->beConstructedWith('input', 'error', false);
        $this->isPurgeOnStartup()->shouldReturn(false);
    }

    function it_throws_if_input_queue_is_empty()
    {
        $this->beConstructedWith('', 'error', false);
        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_if_error_queue_is_empty()
    {
        $this->beConstructedWith('input', '', false);
        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }
}
