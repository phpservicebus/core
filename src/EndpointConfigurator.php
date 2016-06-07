<?php
namespace PSB\Core;


use Acclimate\Container\ContainerAcclimator;
use PSB\Core\Correlation\MessageCorrelationFeature;
use PSB\Core\ErrorHandling\ErrorLastResort\ErrorLastResortFeature;
use PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryFeature;
use PSB\Core\Exception\UnexpectedValueException;
use PSB\Core\Feature\FeatureSettingsExtensions;
use PSB\Core\Feature\RootFeature;
use PSB\Core\MessageMutation\IncomingMessageMutationFeature;
use PSB\Core\MessageMutation\OutgoingMessageMutationFeature;
use PSB\Core\ObjectBuilder\Builder;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\ObjectBuilder\Container;
use PSB\Core\Outbox\OutboxFeature;
use PSB\Core\Persistence\EnabledPersistence;
use PSB\Core\Persistence\InMemory\InMemoryPersistenceDefinition;
use PSB\Core\Persistence\InMemory\Outbox\InMemoryOutboxPersistenceFeature;
use PSB\Core\Persistence\PersistenceDefinition;
use PSB\Core\Persistence\PersistenceDefinitionApplier;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Pipeline\BusOperations;
use PSB\Core\Pipeline\BusOperationsContextFactory;
use PSB\Core\Pipeline\Incoming\IncomingContextFactory;
use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Pipeline\IncomingPipelineFeature;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\OutgoingPipelineFeature;
use PSB\Core\Pipeline\PipelineFactory;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Pipeline\PipelineRootStageContext;
use PSB\Core\Pipeline\StepChainBuilderFactory;
use PSB\Core\Routing\AutoSubscription\AutoSubscribeFeature;
use PSB\Core\Routing\RoutingFeature;
use PSB\Core\Routing\UnicastRoutingTable;
use PSB\Core\Serialization\Json\JsonSerializationDefinition;
use PSB\Core\Serialization\SerializationConfigurator;
use PSB\Core\Serialization\SerializationDefinition;
use PSB\Core\Serialization\SerializationFeature;
use PSB\Core\Transport\Config\TransportDefinition;
use PSB\Core\Transport\InboundTransport;
use PSB\Core\Transport\MessagePusherInterface;
use PSB\Core\Transport\OutboundTransport;
use PSB\Core\Transport\PushPipe;
use PSB\Core\Transport\PushSettings;
use PSB\Core\Transport\QueueBindings;
use PSB\Core\Transport\RabbitMq\Config\RabbitMqTransportDefinition;
use PSB\Core\Transport\ReceivingFeature;
use PSB\Core\Transport\SendingFeature;
use PSB\Core\Transport\TransportFeature;
use PSB\Core\Transport\TransportReceiveContextFactory;
use PSB\Core\Transport\TransportReceiver;
use PSB\Core\Util\Clock\ClockInterface;
use PSB\Core\Util\Clock\SystemClock;
use PSB\Core\Util\Guard;
use PSB\Core\Util\Settings;
use PSB\Core\UuidGeneration\Comb\CombUuidGenerationDefinition;
use PSB\Core\UuidGeneration\UuidGenerationConfigurator;
use PSB\Core\UuidGeneration\UuidGenerationDefinition;
use PSB\Core\UuidGeneration\UuidGeneratorInterface;

class EndpointConfigurator
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var string
     */
    private $endpointName;

    /**
     * @var MessageHandlerRegistry
     */
    private $messageHandlerRegistry;

    /**
     * @var UnicastRoutingTable
     */
    private $unicastRoutingTable;

    /**
     * @var MessageMutatorRegistry
     */
    private $messageMutatorRegistry;

    /**
     * @var PipelineModifications
     */
    private $pipelineModifications;

    /**
     * @param string                 $endpointName
     * @param Settings               $settings
     * @param MessageHandlerRegistry $messageHandlerRegistry
     * @param UnicastRoutingTable    $unicastRoutingTable
     * @param MessageMutatorRegistry $messageMutatorRegistry
     * @param PipelineModifications  $pipelineModifications
     */
    public function __construct(
        $endpointName,
        Settings $settings,
        MessageHandlerRegistry $messageHandlerRegistry,
        UnicastRoutingTable $unicastRoutingTable,
        MessageMutatorRegistry $messageMutatorRegistry,
        PipelineModifications $pipelineModifications
    ) {
        Guard::againstNullAndEmpty('name', $endpointName);
        $this->endpointName = $endpointName;
        $this->settings = $settings;
        $this->messageHandlerRegistry = $messageHandlerRegistry;
        $this->unicastRoutingTable = $unicastRoutingTable;
        $this->messageMutatorRegistry = $messageMutatorRegistry;
        $this->pipelineModifications = $pipelineModifications;
    }

    /**
     * @param string $endpointName
     *
     * @return EndpointConfigurator
     */
    public static function create($endpointName)
    {
        return new self(
            $endpointName,
            new Settings(),
            new MessageHandlerRegistry(),
            new UnicastRoutingTable(),
            new MessageMutatorRegistry(),
            new PipelineModifications()
        );
    }

    /**
     * @param string $endpointName
     */
    public function setEndpointName($endpointName)
    {
        $this->endpointName = $endpointName;
    }

    /**
     * @param PersistenceDefinition $definition
     * @param StorageType|null      $storageType
     *
     * @return Persistence\PersistenceConfigurator
     */
    public function usePersistence(PersistenceDefinition $definition, StorageType $storageType = null)
    {
        $enabledPersistences = $this->settings->tryGet(KnownSettingsEnum::ENABLED_PERSISTENCES) ?: [];

        $enabledPersistences[] = new EnabledPersistence($definition, $storageType);

        $this->settings->set(KnownSettingsEnum::ENABLED_PERSISTENCES, $enabledPersistences);

        return $definition->createConfigurator($this->settings);
    }

    public function clearPersistences()
    {
        $this->settings->set(KnownSettingsEnum::ENABLED_PERSISTENCES, []);
    }

    /**
     * @param TransportDefinition $definition
     *
     * @return Transport\Config\TransportConfigurator
     */
    public function useTransport(TransportDefinition $definition)
    {
        $this->settings->set(TransportDefinition::class, $definition);
        $this->settings->set(InboundTransport::class, new InboundTransport());
        $this->settings->set(OutboundTransport::class, new OutboundTransport());

        return $definition->createConfigurator($this->settings);
    }

    /**
     * @param SerializationDefinition $definition
     *
     * @return SerializationConfigurator
     */
    public function useSerialization(SerializationDefinition $definition)
    {
        $this->settings->set(SerializationDefinition::class, $definition);

        return $definition->createConfigurator($this->settings);
    }

    /**
     * @param UuidGenerationDefinition $definition
     *
     * @return UuidGenerationConfigurator
     */
    public function useUuidGeneration(UuidGenerationDefinition $definition)
    {
        $this->settings->set(UuidGenerationDefinition::class, $definition);

        return $definition->createConfigurator($this->settings);
    }

    /**
     * Using your own container allows you to register handlers as services.
     * Supported containers are the same as those supported by jeremeamia/acclimate-container.
     *
     * @param mixed $container
     */
    public function useContainer($container)
    {
        $this->settings->set(KnownSettingsEnum::CONTAINER, $container);
    }

    /**
     * @param string $eventFqcn
     * @param string $handlerContainerId
     */
    public function registerEventHandler($eventFqcn, $handlerContainerId)
    {
        $this->messageHandlerRegistry->registerEventHandler($eventFqcn, $handlerContainerId);
    }

    /**
     * @param string $commandFqcn
     * @param string $handlerContainerId
     */
    public function registerCommandHandler($commandFqcn, $handlerContainerId)
    {
        $this->messageHandlerRegistry->registerCommandHandler($commandFqcn, $handlerContainerId);
    }

    /**
     * @param string $mutatorContainerId
     */
    public function registerIncomingLogicalMessageMutator($mutatorContainerId)
    {
        $this->messageMutatorRegistry->registerIncomingLogicalMessageMutator($mutatorContainerId);
    }

    /**
     * @param string $mutatorContainerId
     */
    public function registerIncomingPhysicalMessageMutator($mutatorContainerId)
    {
        $this->messageMutatorRegistry->registerIncomingPhysicalMessageMutator($mutatorContainerId);
    }

    /**
     * @param string $mutatorContainerId
     */
    public function registerOutgoingLogicalMessageMutator($mutatorContainerId)
    {
        $this->messageMutatorRegistry->registerOutgoingLogicalMessageMutator($mutatorContainerId);
    }

    /**
     * @param string $mutatorContainerId
     */
    public function registerOutgoingPhysicalMessageMutator($mutatorContainerId)
    {
        $this->messageMutatorRegistry->registerOutgoingPhysicalMessageMutator($mutatorContainerId);
    }

    /**
     * It allows you to configure which endpoints should receive a command message.
     * Command messages are those being sent via ->send and not via ->publish.
     *
     * @param string $messageFqcn
     * @param string $endpointName
     */
    public function registerCommandRoutingRule($messageFqcn, $endpointName)
    {
        $this->unicastRoutingTable->routeToEndpoint($messageFqcn, $endpointName);
    }

    /**
     * @param string $stepId
     */
    public function removePipelineStep($stepId)
    {
        $this->pipelineModifications->removeStep($stepId);
    }

    /**
     * @param string        $stepId
     * @param string        $stepFqcn
     * @param callable|null $factory
     * @param string|null   $description
     */
    public function replacePipelineStep($stepId, $stepFqcn, callable $factory = null, $description = null)
    {
        $this->pipelineModifications->replaceStep($stepId, $stepFqcn, $factory, $description);
    }

    /**
     * @param string        $stepId
     * @param string        $stepFqcn
     * @param callable|null $factory
     * @param string|null   $description
     *
     * @return Pipeline\StepRegistration
     */
    public function registerPipelineStep($stepId, $stepFqcn, callable $factory = null, $description = null)
    {
        return $this->pipelineModifications->registerStep($stepId, $stepFqcn, $factory, $description);
    }

    /**
     * Installers are supposed to be tasks that only need to be ran once per deployment, like creating endpoint queues,
     * persistence related database tables, etc. They are disabled by default and they need to be explicitly enabled
     * if needed.
     */
    public function enableInstallers()
    {
        $this->settings->set(KnownSettingsEnum::INSTALLERS_ENABLED, true);
    }

    public function disableInstallers()
    {
        $this->settings->set(KnownSettingsEnum::INSTALLERS_ENABLED, false);
    }

    /**
     * @return bool
     */
    public function areInstallersEnabled()
    {
        return $this->settings->tryGet(KnownSettingsEnum::INSTALLERS_ENABLED) ?: false;
    }

    /**
     * Putting the endpoint in send only mode disables any message receiving capabilities.
     * This also means that the endpoint will no longer block waiting for messages when started.
     */
    public function enableSendOnly()
    {
        $this->settings->set(KnownSettingsEnum::SEND_ONLY, true);
    }

    /**
     * @return bool
     */
    public function isSendOnly()
    {
        return $this->settings->tryGet(KnownSettingsEnum::SEND_ONLY) ?: false;
    }

    /**
     * @param string $errorQueue
     */
    public function sendFailedMessagesTo($errorQueue)
    {
        $this->settings->set(KnownSettingsEnum::ERROR_QUEUE, $errorQueue);
    }

    public function enableDurableMessaging()
    {
        $this->settings->set(KnownSettingsEnum::DURABLE_MESSAGING_ENABLED, true);
    }

    public function disableDurableMessaging()
    {
        $this->settings->set(KnownSettingsEnum::DURABLE_MESSAGING_ENABLED, false);
    }

    /**
     * @param int $days
     */
    public function setDaysToKeepOutboxDeduplicationData($days)
    {
        $this->settings->set(KnownSettingsEnum::DAYS_TO_KEEP_DEDUPLICATION_DATA, $days);
    }

    /**
     * @param string $featureFqcn
     */
    public function enableFeature($featureFqcn)
    {
        FeatureSettingsExtensions::enableFeature($featureFqcn, $this->settings);
    }

    /**
     * @param string $featureFqcn
     */
    public function disableFeature($featureFqcn)
    {
        FeatureSettingsExtensions::disableFeature($featureFqcn, $this->settings);
    }

    /**
     * @param int $maxRetries
     */
    public function setMaxFirstLevelRetries($maxRetries)
    {
        Guard::againstNullAndNonInt('maxRetries', $maxRetries);
        $this->settings->set(KnownSettingsEnum::MAX_FLR_RETRIES, (int)$maxRetries);
    }

    /**
     * @return StartableEndpoint
     */
    public function build()
    {
        $externalContainer = $this->settings->tryGet(KnownSettingsEnum::CONTAINER);
        $container = new Container();
        $acclimator = new ContainerAcclimator();
        $adaptedExternalContainer = $externalContainer ? $acclimator->acclimate($externalContainer) : null;
        $builder = new Builder($container, $adaptedExternalContainer);
        $container[BuilderInterface::class] = $builder;

        $this->ensureTransportConfigured();
        $this->ensureOutboxPersistenceConfigured();
        $this->ensureSerializationConfigured();
        $this->ensureUuidGenerationConfigured();
        $this->registerKnownFeatures();

        $this->settings->setDefault(KnownSettingsEnum::ENDPOINT_NAME, $this->endpointName);
        $this->settings->setDefault(KnownSettingsEnum::SEND_ONLY, false);
        $this->settings->set(QueueBindings::class, new QueueBindings());

        $persistenceDefinitionApplier = new PersistenceDefinitionApplier();

        $this->registerBaseContainerServices($container);

        return new StartableEndpoint(
            $this->settings,
            $persistenceDefinitionApplier,
            $builder,
            $builder->build(PipelineModifications::class),
            $builder->build(BusContext::class)
        );
    }

    private function ensureTransportConfigured()
    {
        if (!$this->settings->tryGet(TransportDefinition::class)) {
            $this->useTransport(new RabbitMqTransportDefinition());
        }
    }

    private function ensureOutboxPersistenceConfigured()
    {
        $isConfigured = false;
        /** @var EnabledPersistence[] $enabledPersistences */
        $enabledPersistences = $this->settings->tryGet(KnownSettingsEnum::ENABLED_PERSISTENCES) ?: [];
        foreach ($enabledPersistences as $enabledPersistence) {
            $selectedStorageType = $enabledPersistence->getSelectedStorageType();
            if (!$selectedStorageType
                || $selectedStorageType && $selectedStorageType->equals(StorageType::OUTBOX())
            ) {
                $isConfigured = true;
            }
        }

        if (!$isConfigured) {
            $this->usePersistence(new InMemoryPersistenceDefinition(), StorageType::OUTBOX());
        }
    }

    private function ensureSerializationConfigured()
    {
        if (!$this->settings->tryGet(SerializationDefinition::class)) {
            $this->useSerialization(new JsonSerializationDefinition());
        }
    }

    private function ensureUuidGenerationConfigured()
    {
        if (!$this->settings->tryGet(UuidGenerationDefinition::class)) {
            $this->useUuidGeneration(new CombUuidGenerationDefinition());
        }
    }

    private function registerBaseContainerServices(Container $c)
    {
        $c[Settings::class] = $this->settings;
        $c[MessageHandlerRegistry::class] = $this->messageHandlerRegistry;
        $c[MessageMutatorRegistry::class] = $this->messageMutatorRegistry;
        $c[UnicastRoutingTable::class] = $this->unicastRoutingTable;
        $c[PipelineModifications::class] = $this->pipelineModifications;

        /** @var UuidGenerationDefinition $uuidDefinition */
        $uuidDefinition = $this->settings->get(UuidGenerationDefinition::class);
        $c[UuidGeneratorInterface::class] = $uuidDefinition->formalize($this->settings);

        $c[PipelineFactory::class] = new PipelineFactory($c[BuilderInterface::class], new StepChainBuilderFactory());
        $c[BusOperationsContextFactory::class] = new BusOperationsContextFactory();
        $c[BusOperations::class] = new BusOperations(
            $c[PipelineFactory::class],
            $c[BusOperationsContextFactory::class],
            $c[PipelineModifications::class],
            $c[UuidGeneratorInterface::class]
        );
        $c[OutgoingOptionsFactory::class] = new OutgoingOptionsFactory();
        $c[BusContext::class] = new BusContext(
            new PipelineRootStageContext($c[BuilderInterface::class]),
            $c[BusOperations::class],
            $c[OutgoingOptionsFactory::class]
        );

        $c[IncomingContextFactory::class] = function ($c) {
            return new IncomingContextFactory($c[BusOperations::class], $c[OutgoingOptionsFactory::class]);
        };
        $c[OutgoingContextFactory::class] = function () {
            return new OutgoingContextFactory();
        };

        $c[ClockInterface::class] = function () {
            return new SystemClock();
        };
        $c[DateTimeConverter::class] = function () {
            return new DateTimeConverter();
        };

        $c[PushPipe::class] = function ($c) {
            /** @var PipelineFactory $pipelineFactory */
            $pipelineFactory = $c[PipelineFactory::class];
            return new PushPipe(
                new TransportReceiveContextFactory($c[BuilderInterface::class]),
                $pipelineFactory->createStartingWith(
                    TransportReceiveContext::class,
                    $c[PipelineModifications::class]
                )
            );
        };
        $c[PushSettings::class] = function () {
            $errorQueue = $this->settings->tryGet(KnownSettingsEnum::ERROR_QUEUE);
            if (!$errorQueue) {
                throw new UnexpectedValueException(
                    "The error queue needs to be set. You can do it using endpointConfigurator.sendFailedMessagesTo."
                );
            }
            return new PushSettings(
                $this->settings->get(KnownSettingsEnum::LOCAL_ADDRESS),
                $this->settings->get(KnownSettingsEnum::ERROR_QUEUE),
                $this->settings->tryGet(KnownSettingsEnum::PURGE_ON_STARTUP) ?: false
            );
        };
        $c[TransportReceiver::class] = function ($c) {
            return new TransportReceiver(
                $c[MessagePusherInterface::class],
                $c[PushSettings::class],
                $c[PushPipe::class]
            );
        };
    }

    private function registerKnownFeatures()
    {
        $featureFqcnList = [
            RootFeature::class,
            TransportFeature::class,
            SendingFeature::class,
            ReceivingFeature::class,
            SerializationFeature::class,
            RoutingFeature::class,
            AutoSubscribeFeature::class,
            OutgoingPipelineFeature::class,
            IncomingPipelineFeature::class,
            OutboxFeature::class,
            InMemoryOutboxPersistenceFeature::class,
            OutgoingMessageMutationFeature::class,
            IncomingMessageMutationFeature::class,
            ErrorLastResortFeature::class,
            FirstLevelRetryFeature::class,
            MessageCorrelationFeature::class,
        ];

        foreach ($featureFqcnList as $featureFqcn) {
            FeatureSettingsExtensions::registerFeature($featureFqcn, $this->settings);
        }
    }
}
