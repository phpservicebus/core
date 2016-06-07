<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Pipeline\StepChainBuilder;
use PSB\Core\Pipeline\StepChainBuilderFactory;

/**
 * @mixin StepChainBuilderFactory
 */
class StepChainBuilderFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\StepChainBuilderFactory');
    }

    function it_creates_a_step_chain_builder(PipelineModifications $pipelineModifications)
    {
        $stageContextFqcn = 'irrelevant';
        $pipelineModifications->getAdditions()->willReturn(['addition']);
        $pipelineModifications->getRemovals()->willReturn(['removal']);
        $pipelineModifications->getReplacements()->willReturn(['replacement']);
        $this->createChainBuilder($stageContextFqcn, $pipelineModifications)->shouldBeLike(
            new StepChainBuilder(
                $stageContextFqcn,
                ['addition'],
                ['replacement'],
                ['removal']
            )
        );
    }
}
