<?php
namespace PSB\Core\Persistence\InMemory\Outbox;


use PSB\Core\Feature\Feature;
use PSB\Core\Feature\FeatureSettingsExtensions;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Outbox\OutboxFeature;
use PSB\Core\Outbox\OutboxStorageInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class InMemoryOutboxPersistenceFeature extends Feature
{

    /**
     * Method will always be executed and should be used to determine whether to enable or disable the feature,
     * configure default settings, configure dependencies, configure prerequisites and register startup tasks.
     */
    public function describe()
    {
        $this->dependsOn(OutboxFeature::class);
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
        $outboxEnabled = FeatureSettingsExtensions::isFeatureActive(OutboxFeature::class, $settings);

        if ($outboxEnabled) {
            $builder->defineSingleton(
                OutboxStorageInterface::class,
                function () use ($builder, $settings) {
                    return new InMemoryOutboxStorage();
                }
            );
        }
    }
}
