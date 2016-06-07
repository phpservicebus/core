<?php
namespace PSB\Core\Pipeline;


use PSB\Core\DateTimeConverter;
use PSB\Core\Feature\Feature;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Outgoing\ImmediateDispatchTerminator;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\OutgoingPhysicalToDispatchConnector;
use PSB\Core\Transport\MessageDispatcherInterface;
use PSB\Core\Transport\SendingFeature;
use PSB\Core\Util\Clock\ClockInterface;
use PSB\Core\Util\Settings;

class OutgoingPipelineFeature extends Feature
{

    /**
     * Method will always be executed and should be used to determine whether to enable or disable the feature,
     * configure default settings, configure dependencies, configure prerequisites and register startup tasks.
     */
    public function describe()
    {
        $this->enableByDefault();
        $this->dependsOn(SendingFeature::class);
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
    public function setup(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $pipelineModifications->registerStep(
            'OutgoingPhysicalToDispatchConnector',
            OutgoingPhysicalToDispatchConnector::class,
            function () use ($builder) {
                return new OutgoingPhysicalToDispatchConnector(
                    $builder->build(OutgoingContextFactory::class),
                    $builder->build(DateTimeConverter::class),
                    $builder->build(ClockInterface::class)
                );
            }
        );
        $pipelineModifications->registerStep(
            'ImmediateDispatchTerminator',
            ImmediateDispatchTerminator::class,
            function () use ($builder) {
                return new ImmediateDispatchTerminator($builder->build(MessageDispatcherInterface::class));
            }
        );
    }
}
