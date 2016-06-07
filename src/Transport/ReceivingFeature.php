<?php
namespace PSB\Core\Transport;


use PSB\Core\Feature\Feature;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class ReceivingFeature extends Feature
{

    /**
     * Method will always be executed and should be used to determine whether to enable or disable the feature,
     * configure default settings, configure dependencies, configure prerequisites and register startup tasks.
     */
    public function describe()
    {
        $this->enableByDefault();
        $this->dependsOn(TransportFeature::class);
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
        /** @var QueueBindings $queueBindings */
        $queueBindings = $settings->get(QueueBindings::class);
        $queueBindings->bindReceiving($settings->get(KnownSettingsEnum::LOCAL_ADDRESS));

        /** @var InboundTransport $inboundTransport */
        $inboundTransport = $settings->get(InboundTransport::class);
        $receiveInfrastructure = $inboundTransport->configure($settings);

        $builder->defineSingleton(MessagePusherInterface::class, $receiveInfrastructure->getMessagePusherFactory());
        $builder->defineSingleton(QueueCreatorInterface::class, $receiveInfrastructure->getQueueCreatorFactory());

        $this->registerInstallTask(
            function () use ($builder, $settings) {
                return new QueueCreatorFeatureInstallTask($builder->build(QueueCreatorInterface::class), $settings);
            }
        );
    }
}
