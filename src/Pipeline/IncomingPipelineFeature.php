<?php
namespace PSB\Core\Pipeline;


use PSB\Core\Feature\Feature;
use PSB\Core\MessageHandlerRegistry;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Incoming\IncomingContextFactory;
use PSB\Core\Pipeline\Incoming\InvokeHandlerTerminator;
use PSB\Core\Pipeline\Incoming\LoadHandlersConnector;
use PSB\Core\Util\Settings;

class IncomingPipelineFeature extends Feature
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
    public function setup(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $pipelineModifications->registerStep(
            'LoadHandlersConnector',
            LoadHandlersConnector::class,
            function () use ($builder) {
                return new LoadHandlersConnector(
                    $builder->build(MessageHandlerRegistry::class),
                    $builder->build(IncomingContextFactory::class)
                );
            }
        );
        $pipelineModifications->registerStep(
            'InvokeHandlerTerminator',
            InvokeHandlerTerminator::class,
            function () use ($builder) {
                return new InvokeHandlerTerminator();
            }
        );
    }
}
