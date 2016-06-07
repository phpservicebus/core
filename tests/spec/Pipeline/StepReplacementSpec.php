<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\StepReplacement;

/**
 * @mixin StepReplacement
 */
class StepReplacementSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('id', 'class');
        $this->shouldHaveType('PSB\Core\Pipeline\StepReplacement');
    }

    function it_contains_the_id_set_at_construction()
    {
        $this->beConstructedWith('id', 'class');

        $this->getIdToReplace()->shouldReturn('id');
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

    function it_can_register_the_step_in_the_builder_if_it_has_a_factory(BuilderInterface $builder)
    {
        $factory = function () {
        };
        $this->beConstructedWith('id', 'class', $factory);

        $builder->defineSingleton('class', $factory)->shouldBeCalled();

        $this->registerInBuilder($builder);
    }

    function it_throws_if_constructed_with_empty_id()
    {
        $this->beConstructedWith('', 'class');

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_if_constructed_with_empty_class()
    {
        $this->beConstructedWith('id', '');

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }
}
