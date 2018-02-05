<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;

use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\StepRegistration;
use PSB\Core\Pipeline\StepRegistrationDependency;
use PSB\Core\Pipeline\StepReplacement;

/**
 * @mixin StepRegistration
 */
class StepRegistrationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('id', 'class');
        $this->shouldHaveType('PSB\Core\Pipeline\StepRegistration');
    }

    function it_contains_the_id_set_at_construction()
    {
        $this->beConstructedWith('id', 'class');

        $this->getStepId()->shouldReturn('id');
    }

    function it_contains_the_class_set_at_construction()
    {
        $this->beConstructedWith('id', 'class');

        $this->getStepFqcn()->shouldReturn('class');
    }

    function it_contains_the_description_set_at_construction()
    {
        $this->beConstructedWith('id', 'class', null, 'desc');

        $this->getDescription()->shouldReturn('desc');
    }

    function it_contains_the_factory_set_at_construction()
    {
        $factory = function () {
        };
        $this->beConstructedWith('id', 'class', $factory);

        $this->getFactory()->shouldReturn($factory);
    }

    function it_can_be_replaced_by_a_step_replacement()
    {
        $this->beConstructedWith(
            'id',
            'class',
            function () {
            }
        );
        $replacementFactory = null;
        $replacement = new StepReplacement('id', 'otherclass', null, 'otherdesc');
        $this->replaceWith($replacement);

        $this->getStepFqcn()->shouldReturn('otherclass');
        $this->getDescription()->shouldReturn('otherdesc');
        $this->getFactory()->shouldReturn(null);
    }

    function it_can_register_the_step_in_the_builder_if_it_has_a_factory(BuilderInterface $builder)
    {
        $factory = function () {
        };
        $this->beConstructedWith('id', 'class', $factory);

        $builder->defineSingleton('class', $factory)->shouldBeCalled();

        $this->registerInBuilder($builder);
    }

    function it_can_insert_a_before_dependency()
    {
        $this->beConstructedWith('id', 'class');
        $this->insertBefore('otherid');

        $this->getBefores()->shouldBeLike([new StepRegistrationDependency('id', 'otherid', true)]);
    }

    function it_can_insert_an_after_dependency()
    {
        $this->beConstructedWith('id', 'class');
        $this->insertAfter('otherid');

        $this->getAfters()->shouldBeLike([new StepRegistrationDependency('id', 'otherid', true)]);
    }

    function it_throws_if_constructed_with_empty_step_id()
    {
        $this->beConstructedWith('', 'class');

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_if_constructed_with_empty_step_class()
    {
        $this->beConstructedWith('id', '');

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_if_inserting_before_with_empty_id()
    {
        $this->beConstructedWith('id', 'class');

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInsertBefore('');
    }

    function it_throws_if_inserting_after_with_empty_id()
    {
        $this->beConstructedWith('id', 'class');

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInsertAfter('');
    }
}
