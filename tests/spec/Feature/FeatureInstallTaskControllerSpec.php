<?php

namespace spec\PSB\Core\Feature;

use PhpSpec\ObjectBehavior;

use PSB\Core\Feature\FeatureInstallTaskController;
use PSB\Core\Feature\FeatureInstallTaskInterface;
use PSB\Core\ObjectBuilder\BuilderInterface;
use specsupport\PSB\Core\ParametrizedCallable;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin FeatureInstallTaskController
 */
class FeatureInstallTaskControllerSpec extends ObjectBehavior
{
    function it_is_initializable(SimpleCallable $taskFactory)
    {
        $this->beConstructedWith($taskFactory);
        $this->shouldHaveType('PSB\Core\Feature\FeatureInstallTaskController');
    }

    function it_creates_the_task_using_the_factory_and_runs_it(
        ParametrizedCallable $taskFactory,
        BuilderInterface $builder,
        FeatureInstallTaskInterface $installTask
    ) {
        $this->beConstructedWith($taskFactory);
        $taskFactory->__invoke($builder)->willReturn($installTask);

        $installTask->install()->shouldBeCalled();

        $this->install($builder);
    }
}
