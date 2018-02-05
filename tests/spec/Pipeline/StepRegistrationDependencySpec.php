<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;

use PSB\Core\Pipeline\StepRegistrationDependency;

/**
 * @mixin StepRegistrationDependency
 */
class StepRegistrationDependencySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('id1', 'id2', true);
        $this->shouldHaveType('PSB\Core\Pipeline\StepRegistrationDependency');
    }

    function it_contains_the_dependant_id_set_at_construction()
    {
        $this->beConstructedWith('id1', 'id2', true);

        $this->getDependantId()->shouldReturn('id1');
    }

    function it_contains_the_depends_on_id_set_at_construction()
    {
        $this->beConstructedWith('id1', 'id2', true);

        $this->getDependsOnId()->shouldReturn('id2');
    }

    function it_contains_the_is_enforced_set_at_construction()
    {
        $this->beConstructedWith('id1', 'id2', true);

        $this->isEnforced()->shouldReturn(true);
    }

    function it_throws_if_constructed_with_empty_dependant_id()
    {
        $this->beConstructedWith('', 'id2', true);

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_if_constructed_with_empty_depends_on_id()
    {
        $this->beConstructedWith('id1', '', true);

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }
}
