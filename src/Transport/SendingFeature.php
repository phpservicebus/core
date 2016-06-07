<?php
namespace PSB\Core\Transport;


use PSB\Core\Feature\Feature;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class SendingFeature extends Feature
{

    /**
     * Method will always be executed and should be used to determine whether to enable or disable the feature,
     * configure default settings, configure dependencies, configure prerequisites and register startup tasks.
     */
    public function describe()
    {
        $this->enableByDefault();
        $this->dependsOn(TransportFeature::class);
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
        /** @var OutboundTransport $outboundTransport */
        $outboundTransport = $settings->get(OutboundTransport::class);
        $sendInfrastructure = $outboundTransport->configure($settings);

        $builder->defineSingleton(
            MessageDispatcherInterface::class,
            $sendInfrastructure->getMessageDispatcherFactory()
        );
    }
}
