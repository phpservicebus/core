<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;

use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Pipeline;
use PSB\Core\Pipeline\PipelineFactory;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Pipeline\StepChainBuilder;
use PSB\Core\Pipeline\StepChainBuilderFactory;
use PSB\Core\Pipeline\StepRegistration;

/**
 * @mixin PipelineFactory
 */
class PipelineFactorySpec extends ObjectBehavior
{
    function it_is_initializable(BuilderInterface $builder, StepChainBuilderFactory $chainBuilderFactory)
    {
        $this->beConstructedWith($builder, $chainBuilderFactory);
        $this->shouldHaveType('PSB\Core\Pipeline\PipelineFactory');
    }

    function it_creates_a_pipeline_starting_from_a_context(
        BuilderInterface $builder,
        StepChainBuilderFactory $chainBuilderFactory,
        PipelineModifications $pipelineModifications,
        StepChainBuilder $stepChainBuilder
    ) {
        $stageContextFqcn = 'irrelevant';
        $this->beConstructedWith($builder, $chainBuilderFactory);
        $chainBuilderFactory->createChainBuilder($stageContextFqcn, $pipelineModifications)->willReturn(
            $stepChainBuilder
        );
        $stepChainBuilder->build()->willReturn(
            [new StepRegistration('id1', 'fqcn1'), new StepRegistration('id2', 'fqcn2')]
        );
        $builder->build('fqcn1')->willReturn('instance1');
        $builder->build('fqcn2')->willReturn('instance2');

        $this->createStartingWith($stageContextFqcn, $pipelineModifications)->shouldBeLike(
            new Pipeline(['instance1', 'instance2'])
        );
    }
}
