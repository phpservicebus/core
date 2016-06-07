<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineRootStageContext;

/**
 * @mixin PipelineRootStageContext
 */
class PipelineRootStageContextSpec extends ObjectBehavior
{
    function it_contains_the_builder_set_at_construction(BuilderInterface $builder)
    {
        $this->beConstructedWith($builder);
        $this->getBuilder()->shouldReturn($builder);
    }
}
