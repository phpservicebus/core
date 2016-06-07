<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineStageContext;
use spec\PSB\Core\Pipeline\PipelineStageContextSpec\SampleStageContext;

/**
 * @mixin PipelineStageContext
 */
class PipelineStageContextSpec extends ObjectBehavior
{
    function it_throws_when_getting_the_builder_if_none_registered()
    {
        $this->beAnInstanceOf(SampleStageContext::class);
        $this->shouldThrow()->duringGetBuilder();
    }

    function it_gets_a_builder_if_one_is_registered()
    {
        $this->beAnInstanceOf(SampleStageContext::class);
        $this->set(BuilderInterface::class, 'something');
        $this->getBuilder()->shouldReturn('something');
    }
}

namespace spec\PSB\Core\Pipeline\PipelineStageContextSpec;

use PSB\Core\Pipeline\PipelineStageContext;

class SampleStageContext extends PipelineStageContext
{

}
