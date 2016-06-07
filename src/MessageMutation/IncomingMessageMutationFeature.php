<?php
namespace PSB\Core\MessageMutation;


use PSB\Core\Feature\Feature;
use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingLogicalMessageMutationPipelineStep;
use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingPhysicalMessageMutationPipelineStep;
use PSB\Core\MessageMutatorRegistry;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactory;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class IncomingMessageMutationFeature extends Feature
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
            'IncomingLogicalMessageMutation',
            IncomingLogicalMessageMutationPipelineStep::class,
            function () use ($builder) {
                return new IncomingLogicalMessageMutationPipelineStep(
                    $builder->build(MessageMutatorRegistry::class),
                    $builder->build(IncomingLogicalMessageFactory::class)
                );
            }
        );

        $pipelineModifications->registerStep(
            'IncomingPhysicalMessageMutation',
            IncomingPhysicalMessageMutationPipelineStep::class,
            function () use ($builder) {
                return new IncomingPhysicalMessageMutationPipelineStep($builder->build(MessageMutatorRegistry::class));
            }
        );
    }
}
