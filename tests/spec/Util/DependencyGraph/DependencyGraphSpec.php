<?php

namespace spec\PSB\Core\Util\DependencyGraph;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Util\DependencyGraph\DependencyGraph;

/**
 * @mixin DependencyGraph
 */
class DependencyGraphSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([], []);
        $this->shouldHaveType('PSB\Core\Util\DependencyGraph\DependencyGraph');
    }

    function it_reverse_sorts_if_no_dependencies_exist()
    {
        $this->beConstructedWith(['id1' => 'value1', 'id2' => 'value2'], ['id1' => [], 'id2' => []]);
        $this->sort()->shouldReturn(['value2', 'value1']);
    }

    function it_sorts_based_on_dependencies_if_they_exist()
    {
        $this->beConstructedWith(
            ['id1' => 'value1', 'id2' => 'value2', 'id3' => 'value3', 'id4' => 'value4'],
            ['id1' => ['id2', 'id3'], 'id2' => [], 'id3' => ['id2'], 'id4' => []]
        );
        $this->sort()->shouldReturn(['value4', 'value1', 'value3', 'value2']);
    }

    function it_throws_if_it_encounters_a_dependency_cycle_while_sorting()
    {
        $this->beConstructedWith(
            ['id1' => 'value1', 'id2' => 'value2', 'id3' => 'value3', 'id4' => 'value4'],
            ['id1' => ['id2', 'id3'], 'id2' => ['id1'], 'id3' => ['id2'], 'id4' => []]
        );
        $this->shouldThrow('PSB\Core\Exception\DependencyGraphCycleException')->duringSort();
    }
}
