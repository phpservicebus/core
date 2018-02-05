<?php

namespace spec\PSB\Core\Util;

use PhpSpec\ObjectBehavior;

use PSB\Core\Util\Settings;

/**
 * @mixin Settings
 */
class SettingsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Util\Settings');
    }

    function it_can_set_a_key_value_pair()
    {
        $this->set('key', 'value');
        $this->get('key')->shouldReturn('value');
    }

    function it_can_provide_a_default_value_for_a_key()
    {
        $this->setDefault('key', 'value');
        $this->get('key')->shouldReturn('value');
    }

    function it_returns_null_when_trying_to_get_a_nonexistent_key()
    {
        $this->tryGet('key')->shouldReturn(null);
    }

    function it_returns_the_value_when_trying_to_get_an_existent_key()
    {
        $this->set('key', 'value');
        $this->tryGet('key')->shouldReturn('value');
    }

    function it_returns_the_value_when_trying_to_get_a_default_key()
    {
        $this->setDefault('key', 'value');
        $this->tryGet('key')->shouldReturn('value');
    }

    function it_indicates_if_it_has_a_key_in_defaults()
    {
        $this->setDefault('key', 'value');
        $this->has('key')->shouldReturn(true);
    }

    function it_indicates_if_it_has_a_key_in_overrides()
    {
        $this->set('key', 'value');
        $this->has('key')->shouldReturn(true);
    }

    function it_indicates_if_it_doesnt_have_a_key()
    {
        $this->has('key')->shouldReturn(false);
    }

    function it_throws_when_getting_a_nonexistent_key()
    {
        $this->shouldThrow('PSB\Core\Exception\OutOfBoundsException')->duringGet('key');
    }

    function it_throws_if_attempting_to_set_an_override_while_locked()
    {
        $this->preventChanges();
        $this->shouldThrow('PSB\Core\Exception\RuntimeException')->duringSet('key', 'value');
    }

    function it_throws_if_attempting_to_set_a_default_while_locked()
    {
        $this->preventChanges();
        $this->shouldThrow('PSB\Core\Exception\RuntimeException')->duringSetDefault('key', 'value');
    }
}
