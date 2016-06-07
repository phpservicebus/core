<?php

namespace spec\PSB\Core\Feature;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Feature\FeatureDependencyGraphBuilder;
use PSB\Core\Util\DependencyGraph\DependencyGraph;
use spec\PSB\Core\Feature\FeatureDependencyGraphBuilderSpec\Feature1;
use spec\PSB\Core\Feature\FeatureDependencyGraphBuilderSpec\Feature2;
use spec\PSB\Core\Feature\FeatureDependencyGraphBuilderSpec\Feature3;

/**
 * @mixin FeatureDependencyGraphBuilder
 */
class FeatureDependencyGraphBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType('PSB\Core\Feature\FeatureDependencyGraphBuilder');
    }

    function it_builds_graph_based_on_dependencies()
    {
        $feat1 = new Feature1();
        $feat1->describe();
        $feat2 = new Feature2();
        $feat2->describe();
        $feat3 = new Feature3();
        $feat3->describe();
        $this->beConstructedWith([$feat1, $feat2, $feat3]);

        $this->build()->shouldBeLike(
            new DependencyGraph(
                ['f1' => $feat1, 'f2' => $feat2, 'f3' => $feat3],
                ['f1' => ['f3'], 'f2' => ['f1'], 'f3' => []]
            )
        );
    }
}

namespace spec\PSB\Core\Feature\FeatureDependencyGraphBuilderSpec;

use PSB\Core\Feature\Feature;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class Feature1 extends Feature
{
    public function describe()
    {
        $this->dependsOn('f2');
    }

    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
    }

    public function getName()
    {
        return 'f1';
    }
}

class Feature2 extends Feature1
{
    public function describe()
    {
    }

    public function getName()
    {
        return 'f2';
    }
}

class Feature3 extends Feature1
{
    public function describe()
    {
        $this->dependsOn('f1');
    }

    public function getName()
    {
        return 'f3';
    }
}
