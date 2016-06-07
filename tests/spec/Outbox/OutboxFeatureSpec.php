<?php

namespace spec\PSB\Core\Outbox;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Exception\UnexpectedValueException;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Outbox\OutboxFeature;
use PSB\Core\Outbox\Pipeline\OutboxConnector;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

/**
 * @mixin OutboxFeature
 */
class OutboxFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Outbox\OutboxFeature');
    }

    function it_describes_being_enabled_by_default()
    {
        $this->describe();

        $this->isEnabledByDefault()->shouldReturn(true);
    }

    function it_throws_on_setup_if_the_persistence_has_no_support_for_outbox_storage(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $settings->get(KnownSettingsEnum::SUPPORTED_STORAGE_TYPE_VALUES)->willReturn(['']);

        $this->shouldThrow(
            new UnexpectedValueException(
                "Selected persistence doesn't have support for outbox storage. Please select another storage or disable the outbox feature using endpointConfigurator.disableFeature."
            )
        )->duringSetup($settings, $builder, $pipelineModifications);
    }

    function it_registers_the_outbox_connector_as_a_pipeline_step_during_setup(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $settings->get(KnownSettingsEnum::SUPPORTED_STORAGE_TYPE_VALUES)->willReturn([StorageType::OUTBOX]);

        $pipelineModifications->registerStep(
            'OutboxConnector',
            OutboxConnector::class,
            Argument::type('\Closure')
        )->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
