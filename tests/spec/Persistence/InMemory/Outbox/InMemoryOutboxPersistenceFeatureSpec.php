<?php

namespace spec\PSB\Core\Persistence\InMemory\Outbox;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Feature\FeatureStateEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Outbox\OutboxFeature;
use PSB\Core\Outbox\OutboxStorageInterface;
use PSB\Core\Persistence\InMemory\Outbox\InMemoryOutboxPersistenceFeature;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

/**
 * @mixin InMemoryOutboxPersistenceFeature
 */
class InMemoryOutboxPersistenceFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Persistence\InMemory\Outbox\InMemoryOutboxPersistenceFeature');
    }

    function it_described_as_depending_on_outbox()
    {
        $this->describe();
        $this->getDependencies()->shouldReturn([[OutboxFeature::class]]);
    }

    function it_does_not_set_up_if_outbox_is_not_active(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $settings->tryGet(OutboxFeature::class)->willReturn(FeatureStateEnum::INACTIVE);

        $builder->defineSingleton(Argument::any())->shouldNotBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }

    function it_sets_up_if_outbox_is_active(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $settings->tryGet(OutboxFeature::class)->willReturn(FeatureStateEnum::ACTIVE);

        $builder->defineSingleton(OutboxStorageInterface::class, Argument::type('\Closure'))->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
