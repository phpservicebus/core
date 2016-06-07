<?php
namespace PSB\Core\Outbox;


use PSB\Core\Exception\UnexpectedValueException;
use PSB\Core\Feature\Feature;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Outbox\Pipeline\OutboxConnector;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Pipeline\Incoming\IncomingContextFactory;
use PSB\Core\Pipeline\Incoming\TransportOperationsConverter;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\StageContext\DispatchContext;
use PSB\Core\Pipeline\PipelineFactory;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class OutboxFeature extends Feature
{
    /**
     * Method will always be executed and should be used to determine whether to enable or disable the feature,
     * configure default settings, configure dependencies, configure prerequisites and register startup tasks.
     */
    public function describe()
    {
        $this->enableByDefault();
        $this->registerPrerequisite(
            function (Settings $settings) {
                return !$settings->tryGet(KnownSettingsEnum::SEND_ONLY);
            },
            "Endpoint is configured as send only."
        );
    }

    /**
     * Method is called if all defined conditions are met and the feature is marked as enabled.
     * Use this method to configure and initialize all required components for the feature like
     * the steps in the pipeline or the instances/factories in the container.
     *
     * @param Settings              $settings
     * @param BuilderInterface      $builder
     * @param PipelineModifications $pipelineModifications
     */
    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
        $supportedStorageTypes = $settings->get(KnownSettingsEnum::SUPPORTED_STORAGE_TYPE_VALUES);
        if (!in_array(StorageType::OUTBOX, $supportedStorageTypes)) {
            throw new UnexpectedValueException(
                "Selected persistence doesn't have support for outbox storage. " .
                "Please select another storage or disable the outbox feature using endpointConfigurator.disableFeature."
            );
        }

        $pipelineModifications->registerStep(
            'OutboxConnector',
            OutboxConnector::class,
            function () use ($builder) {
                /** @var PipelineFactory $pipelineFactory */
                $pipelineFactory = $builder->build(PipelineFactory::class);
                return new OutboxConnector(
                    $pipelineFactory->createStartingWith(
                        DispatchContext::class,
                        $builder->build(PipelineModifications::class)
                    ),
                    $builder->build(OutboxStorageInterface::class),
                    $builder->build(IncomingContextFactory::class),
                    $builder->build(OutgoingContextFactory::class),
                    new TransportOperationsConverter(new OutboxTransportOperationFactory())
                );
            }
        );
    }
}
