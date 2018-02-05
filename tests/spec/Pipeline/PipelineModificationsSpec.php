<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;

use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Pipeline\StepRegistration;
use PSB\Core\Pipeline\StepRemoval;
use PSB\Core\Pipeline\StepReplacement;

/**
 * @mixin PipelineModifications
 */
class PipelineModificationsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\PipelineModifications');
    }

    function it_can_register_a_step()
    {
        $factory = function () {
        };
        $registration = new StepRegistration('id', 'class', $factory, 'desc');

        $this->registerStep('id', 'class', $factory, 'desc')->shouldBeLike($registration);
        $this->getAdditions()->shouldBeLike([$registration]);
    }

    function it_can_replace_a_step()
    {
        $factory = function () {
        };
        $replacement = new StepReplacement('id', 'class', $factory, 'desc');

        $this->replaceStep('id', 'class', $factory, 'desc')->shouldBeLike($replacement);
        $this->getReplacements()->shouldBeLike([$replacement]);
    }

    function it_can_remove_a_step()
    {
        $removal = new StepRemoval('id');

        $this->removeStep('id')->shouldBeLike($removal);
        $this->getRemovals()->shouldBeLike([$removal]);
    }

    function it_can_register_additions_and_replacements_in_the_builder(BuilderInterface $builder)
    {
        $factory = function () {
        };
        $this->registerStep('id1', 'class1', $factory, 'desc');
        $this->replaceStep('id2', 'class2', $factory, 'desc');

        $builder->defineSingleton('class1', $factory)->shouldBeCalled();
        $builder->defineSingleton('class2', $factory)->shouldBeCalled();

        $this->registerStepsInBuilder($builder);
    }
}
