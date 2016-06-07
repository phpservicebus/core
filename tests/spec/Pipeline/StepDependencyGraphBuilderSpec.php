<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Exception\PipelineBuildingException;
use PSB\Core\Pipeline\StepDependencyGraphBuilder;
use PSB\Core\Pipeline\StepRegistration;
use PSB\Core\Util\DependencyGraph\DependencyGraph;

/**
 * @mixin StepDependencyGraphBuilder
 */
class StepDependencyGraphBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType('PSB\Core\Pipeline\StepDependencyGraphBuilder');
    }

    function it_builds_graph_based_on_dependencies()
    {
        $reg1 = new StepRegistration('id1', 'class');
        $reg1->insertBefore('id3');
        $reg1->insertAfter('id2');
        $reg2 = new StepRegistration('id2', 'class');
        $reg3 = new StepRegistration('id3', 'class');
        $this->beConstructedWith([$reg1, $reg2, $reg3]);

        $this->build()->shouldBeLike(
            new DependencyGraph(
                ['id1' => $reg1, 'id2' => $reg2, 'id3' => $reg3],
                ['id1' => ['id3'], 'id2' => ['id1'], 'id3' => []]
            )
        );
    }

    function it_throws_if_enforced_befores_depend_on_nonexistent_steps()
    {
        $reg1 = new StepRegistration('id1', 'class');
        $reg1->insertBefore('id2');
        $this->beConstructedWith([$reg1]);

        $this->shouldThrow(
            new PipelineBuildingException(
                "Registration 'id2' specified in the insertbefore of the 'id1' step does not exist. Current step ids: id1."
            )
        )->duringBuild();
    }

    function it_does_not_throw_if_non_enforced_befores_depend_on_nonexistent_steps()
    {
        $reg1 = new StepRegistration('id1', 'class');
        $reg1->insertBeforeIfExists('id2');
        $this->beConstructedWith([$reg1]);

        $this->build();
    }

    function it_throws_if_enforced_afters_depend_on_nonexistent_steps()
    {
        $reg1 = new StepRegistration('id1', 'class');
        $reg1->insertAfter('id2');
        $this->beConstructedWith([$reg1]);

        $this->shouldThrow(
            new PipelineBuildingException(
                "Registration 'id2' specified in the insertafter of the 'id1' step does not exist. Current step ids: id1."
            )
        )->duringBuild();
    }

    function it_does_not_throw_if_non_enforced_afters_depend_on_nonexistent_steps()
    {
        $reg1 = new StepRegistration('id1', 'class');
        $reg1->insertAfterIfExists('id2');
        $this->beConstructedWith([$reg1]);

        $this->build();
    }
}
