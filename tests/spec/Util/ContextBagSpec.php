<?php

namespace spec\PSB\Core\Util;

use PhpSpec\ObjectBehavior;

use PSB\Core\Util\ContextBag;

/**
 * @mixin ContextBag
 */
class ContextBagSpec extends ObjectBehavior
{
    function it_is_initializable_with_null_parent()
    {
        $this->shouldHaveType('PSB\Core\Util\ContextBag');
    }

    function it_is_initializable_with_non_null_parent(ContextBag $parent)
    {
        $this->beConstructedWith($parent);
        $this->shouldHaveType('PSB\Core\Util\ContextBag');
    }

    function it_throws_when_getting_a_key_if_key_not_found()
    {
        $this->shouldThrow('PSB\Core\Exception\OutOfBoundsException')->duringGet('nonexistant');
    }

    function it_gets_an_existing_key_from_own_stash_if_it_exists()
    {
        $this->set('key', 'value');
        $this->get('key')->shouldReturn('value');
    }

    function it_gets_an_existing_key_from_the_parent_stash_if_it_does_not_exist_in_own_stash(ContextBag $parent)
    {
        $this->beConstructedWith($parent);
        $parent->tryGet('key')->willReturn('value');

        $this->get('key')->shouldReturn('value');
    }

    function it_removes_a_key_from_own_stash()
    {
        $this->set('key', 'value');
        $this->tryGet('key')->shouldReturn('value');

        $this->remove('key');
        $this->tryGet('key')->shouldReturn(null);
    }
}
