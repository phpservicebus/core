<?php
namespace PSB\Core\Serialization;


use PSB\Core\Feature\Feature;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Incoming\IncomingContextFactory;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactory;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Serialization\Pipeline\DeserializeLogicalMessageConnector;
use PSB\Core\Serialization\Pipeline\SerializeMessageConnector;
use PSB\Core\Util\Settings;

class SerializationFeature extends Feature
{

    /**
     * Method will always be executed and should be used to determine whether to enable or disable the feature,
     * configure default settings, configure dependencies, configure prerequisites and register startup tasks.
     */
    public function describe()
    {
        $this->enableByDefault();
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
        /** @var SerializationDefinition $definition */
        $definition = $settings->get(SerializationDefinition::class);
        $serializerFactory = $definition->formalize($settings);

        $builder->defineSingleton(MessageSerializerInterface::class, $serializerFactory);
        $builder->defineSingleton(
            MessageDeserializerResolver::class,
            function () use ($builder) {
                $serializer = $builder->build(MessageSerializerInterface::class);
                return new MessageDeserializerResolver([$serializer], get_class($serializer));
            }
        );
        $builder->defineSingleton(IncomingLogicalMessageFactory::class, new IncomingLogicalMessageFactory());

        $pipelineModifications->registerStep(
            'DeserializeLogicalMessageConnector',
            DeserializeLogicalMessageConnector::class,
            function () use ($builder) {
                return new DeserializeLogicalMessageConnector(
                    $builder->build(MessageDeserializerResolver::class),
                    $builder->build(IncomingLogicalMessageFactory::class),
                    $builder->build(IncomingContextFactory::class)
                );
            }
        );
        $pipelineModifications->registerStep(
            'SerializeMessageConnector',
            SerializeMessageConnector::class,
            function () use ($builder) {
                return new SerializeMessageConnector(
                    $builder->build(MessageSerializerInterface::class),
                    $builder->build(OutgoingContextFactory::class)
                );
            }
        );
    }
}
