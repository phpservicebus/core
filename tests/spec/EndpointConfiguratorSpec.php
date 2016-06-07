<?php

namespace spec\PSB\Core;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\EndpointConfigurator;
use PSB\Core\Feature\FeatureStateEnum;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\MessageHandlerRegistry;
use PSB\Core\MessageMutatorRegistry;
use PSB\Core\Persistence\EnabledPersistence;
use PSB\Core\Persistence\PersistenceConfigurator;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Pipeline\StepRegistration;
use PSB\Core\Routing\UnicastRoutingTable;
use PSB\Core\Serialization\SerializationDefinition;
use PSB\Core\Transport\Config\TransportDefinition;
use PSB\Core\Transport\InboundTransport;
use PSB\Core\Transport\OutboundTransport;
use PSB\Core\Util\Settings;
use PSB\Core\UuidGeneration\UuidGenerationDefinition;
use spec\PSB\Core\EndpointConfiguratorSpec\TestDefinition;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin EndpointConfigurator
 */
class EndpointConfiguratorSpec extends ObjectBehavior
{
    /**
     * @var Settings
     */
    private $settingsMock;

    /**
     * @var MessageHandlerRegistry
     */
    private $messageHandlerRegistryMock;

    /**
     * @var UnicastRoutingTable
     */
    private $unicastRoutingTableMock;

    /**
     * @var MessageMutatorRegistry
     */
    private $messageMutatorRegistryMock;

    /**
     * @var PipelineModifications
     */
    private $pipelineModificationsMock;

    private $endpointNameMock = 'irrelevantname';

    function let(
        Settings $settings,
        MessageHandlerRegistry $messageHandlerRegistry,
        UnicastRoutingTable $unicastRoutingTable,
        MessageMutatorRegistry $messageMutatorRegistry,
        PipelineModifications $pipelineModifications
    ) {
        $this->settingsMock = $settings;
        $this->messageHandlerRegistryMock = $messageHandlerRegistry;
        $this->unicastRoutingTableMock = $unicastRoutingTable;
        $this->messageMutatorRegistryMock = $messageMutatorRegistry;
        $this->pipelineModificationsMock = $pipelineModifications;
        $this->beConstructedWith(
            $this->endpointNameMock,
            $settings,
            $messageHandlerRegistry,
            $unicastRoutingTable,
            $messageMutatorRegistry,
            $pipelineModifications
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\EndpointConfigurator');
    }

    function it_can_be_constructed_statically()
    {
        $this->beConstructedThrough('create', [$this->endpointNameMock]);
        $this->shouldHaveType('PSB\Core\EndpointConfigurator');
    }

    function it_can_use_a_persistence_definition_without_storage_type()
    {
        $this->settingsMock->tryGet(KnownSettingsEnum::ENABLED_PERSISTENCES)->willReturn(null);

        $this->settingsMock->set(
            KnownSettingsEnum::ENABLED_PERSISTENCES,
            [new EnabledPersistence(new TestDefinition())]
        )->shouldBeCalled();

        $this->usePersistence(new TestDefinition());
    }

    function it_can_use_a_persistence_definition_with_a_storage_type(
        TestDefinition $definition,
        PersistenceConfigurator $persistenceConfigurator
    ) {
        $this->settingsMock->tryGet(KnownSettingsEnum::ENABLED_PERSISTENCES)->willReturn(
            [new EnabledPersistence(new TestDefinition())]
        );

        $this->settingsMock->set(
            KnownSettingsEnum::ENABLED_PERSISTENCES,
            [
                new EnabledPersistence(new TestDefinition()),
                new EnabledPersistence($definition->getWrappedObject(), StorageType::OUTBOX())
            ]
        )->shouldBeCalled();

        $definition->createConfigurator($this->settingsMock)->shouldBeCalled()->willReturn($persistenceConfigurator);

        $this->usePersistence($definition->getWrappedObject(), StorageType::OUTBOX())->shouldReturn(
            $persistenceConfigurator
        );
    }

    function it_can_use_a_transport_definition(TransportDefinition $definition)
    {
        $this->settingsMock->set(TransportDefinition::class, $definition)->shouldBeCalled();
        $this->settingsMock->set(InboundTransport::class, new InboundTransport())->shouldBeCalled();
        $this->settingsMock->set(OutboundTransport::class, new OutboundTransport())->shouldBeCalled();
        $definition->createConfigurator($this->settingsMock)->shouldBeCalled();

        $this->useTransport($definition);
    }

    function it_can_use_a_serialization_definition(SerializationDefinition $definition)
    {
        $this->settingsMock->set(SerializationDefinition::class, $definition)->shouldBeCalled();
        $definition->createConfigurator($this->settingsMock)->shouldBeCalled();

        $this->useSerialization($definition);
    }

    function it_can_use_a_uuid_generation_definition(UuidGenerationDefinition $definition)
    {
        $this->settingsMock->set(UuidGenerationDefinition::class, $definition)->shouldBeCalled();
        $definition->createConfigurator($this->settingsMock)->shouldBeCalled();

        $this->useUuidGeneration($definition);
    }

    function it_can_use_a_container($container)
    {
        $this->settingsMock->set(KnownSettingsEnum::CONTAINER, $container)->shouldBeCalled();

        $this->useContainer($container);
    }

    function it_registers_an_event_handler()
    {
        $eventFqcn = 'irrelevant';
        $handlerContainerId = 'irrelevant';
        $this->messageHandlerRegistryMock->registerEventHandler($eventFqcn, $handlerContainerId)->shouldBeCalled();
        $this->registerEventHandler($eventFqcn, $handlerContainerId);
    }

    function it_registers_a_command_handler()
    {
        $eventFqcn = 'irrelevant';
        $handlerContainerId = 'irrelevant';
        $this->messageHandlerRegistryMock->registerCommandHandler($eventFqcn, $handlerContainerId)->shouldBeCalled();
        $this->registerCommandHandler($eventFqcn, $handlerContainerId);
    }

    function it_registers_an_incoming_logical_message_mutator($mutatorContainerId)
    {
        $this->messageMutatorRegistryMock->registerIncomingLogicalMessageMutator($mutatorContainerId)->shouldBeCalled();
        $this->registerIncomingLogicalMessageMutator($mutatorContainerId);
    }

    function it_registers_an_incoming_physical_message_mutator($mutatorContainerId)
    {
        $this->messageMutatorRegistryMock->registerIncomingPhysicalMessageMutator($mutatorContainerId)->shouldBeCalled(
        );
        $this->registerIncomingPhysicalMessageMutator($mutatorContainerId);
    }

    function it_registers_an_outgoing_logical_message_mutator($mutatorContainerId)
    {
        $this->messageMutatorRegistryMock->registerOutgoingLogicalMessageMutator($mutatorContainerId)->shouldBeCalled();
        $this->registerOutgoingLogicalMessageMutator($mutatorContainerId);
    }

    function it_registers_an_outgoing_physical_message_mutator($mutatorContainerId)
    {
        $this->messageMutatorRegistryMock->registerOutgoingPhysicalMessageMutator($mutatorContainerId)->shouldBeCalled(
        );
        $this->registerOutgoingPhysicalMessageMutator($mutatorContainerId);
    }

    function it_registers_a_command_routing_rule($messageFqcn, $endpointName)
    {
        $this->unicastRoutingTableMock->routeToEndpoint($messageFqcn, $endpointName)->shouldBeCalled();
        $this->registerCommandRoutingRule($messageFqcn, $endpointName);
    }

    function it_removes_a_pipeline_step($stepId)
    {
        $this->pipelineModificationsMock->removeStep($stepId)->shouldBeCalled();
        $this->removePipelineStep($stepId);
    }

    function it_replaces_a_pipeline_step($stepId, $stepFqcn, SimpleCallable $factory, $description)
    {
        $this->pipelineModificationsMock->replaceStep($stepId, $stepFqcn, $factory, $description)->shouldBeCalled();
        $this->replacePipelineStep($stepId, $stepFqcn, $factory, $description);
    }

    function it_register_a_pipeline_step(
        $stepId,
        $stepFqcn,
        SimpleCallable $factory,
        $description,
        StepRegistration $stepRegistration
    ) {
        $this->pipelineModificationsMock->registerStep($stepId, $stepFqcn, $factory, $description)->willReturn(
            $stepRegistration
        );
        $this->registerPipelineStep($stepId, $stepFqcn, $factory, $description)->shouldReturn($stepRegistration);
    }

    function it_enables_installers()
    {
        $this->settingsMock->set(KnownSettingsEnum::INSTALLERS_ENABLED, true)->shouldBeCalled();
        $this->enableInstallers();
    }

    function it_enables_send_only_mode()
    {
        $this->settingsMock->set(KnownSettingsEnum::SEND_ONLY, true)->shouldBeCalled();
        $this->enableSendOnly();
    }

    function it_enables_durable_messaging()
    {
        $this->settingsMock->set(KnownSettingsEnum::DURABLE_MESSAGING_ENABLED, true)->shouldBeCalled();

        $this->enableDurableMessaging();
    }

    function it_disables_durable_messaging()
    {
        $this->settingsMock->set(KnownSettingsEnum::DURABLE_MESSAGING_ENABLED, false)->shouldBeCalled();

        $this->disableDurableMessaging();
    }

    function it_sets_the_days_to_keep_deduplication_data()
    {
        $days = 5;
        $this->settingsMock->set(
            KnownSettingsEnum::DAYS_TO_KEEP_DEDUPLICATION_DATA,
            $days
        )->shouldBeCalled();

        $this->setDaysToKeepOutboxDeduplicationData($days);
    }

    function it_enables_a_feature()
    {
        $featureFqcn = 'irrelevantclass';
        $this->settingsMock->tryGet(KnownSettingsEnum::FEATURE_FQCN_LIST)->willReturn(null);
        $this->settingsMock->set(KnownSettingsEnum::FEATURE_FQCN_LIST, [$featureFqcn => $featureFqcn])->shouldBeCalled(
        );
        $this->settingsMock->set($featureFqcn, FeatureStateEnum::ENABLED)->shouldBeCalled();

        $this->enableFeature($featureFqcn);
    }

    function it_disables_a_feature()
    {
        $featureFqcn = 'irrelevantclass';
        $this->settingsMock->set($featureFqcn, FeatureStateEnum::DISABLED)->shouldBeCalled();

        $this->disableFeature($featureFqcn);
    }
}

namespace spec\PSB\Core\EndpointConfiguratorSpec;

use PSB\Core\Persistence\PersistenceDefinition;
use PSB\Core\Util\Settings;

class TestDefinition extends PersistenceDefinition
{
    public function createConfigurator(Settings $settings)
    {

    }

    public function formalize()
    {
    }
}
