<?php
namespace PSB\Core\MessageMutation;


use PSB\Core\Feature\Feature;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutationPipelineStep;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingPhysicalMessageMutationPipelineStep;
use PSB\Core\MessageMutatorRegistry;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class OutgoingMessageMutationFeature extends Feature
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
        $pipelineModifications->registerStep(
            'OutgoingLogicalMessageMutation',
            OutgoingLogicalMessageMutationPipelineStep::class,
            function () use ($builder) {
                return new OutgoingLogicalMessageMutationPipelineStep($builder->build(MessageMutatorRegistry::class));
            }
        );

        $pipelineModifications->registerStep(
            'OutgoingPhysicalMessageMutation',
            OutgoingPhysicalMessageMutationPipelineStep::class,
            function () use ($builder) {
                return new OutgoingPhysicalMessageMutationPipelineStep($builder->build(MessageMutatorRegistry::class));
            }
        );
    }
}
