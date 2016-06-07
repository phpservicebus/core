<?php

namespace spec\PSB\Core\Feature;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\BusContextInterface;
use PSB\Core\Feature\FeatureStartupTaskController;
use PSB\Core\Feature\FeatureStartupTaskInterface;
use PSB\Core\ObjectBuilder\BuilderInterface;
use specsupport\PSB\Core\ParametrizedCallable;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin FeatureStartupTaskController
 */
class FeatureStartupTaskControllerSpec extends ObjectBehavior
{
    function it_is_initializable(SimpleCallable $taskFactory)
    {
        $this->beConstructedWith($taskFactory);
        $this->shouldHaveType('PSB\Core\Feature\FeatureStartupTaskController');
    }

    function it_creates_the_task_using_the_factory_and_starts_it(
        ParametrizedCallable $taskFactory,
        BuilderInterface $builder,
        BusContextInterface $busContext,
        FeatureStartupTaskInterface $startupTask
    ) {
        $this->beConstructedWith($taskFactory);
        $taskFactory->__invoke($builder)->willReturn($startupTask);

        $startupTask->start($busContext)->shouldBeCalled();

        $this->start($builder, $busContext);
    }
}
