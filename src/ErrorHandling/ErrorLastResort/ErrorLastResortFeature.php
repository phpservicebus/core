<?php
namespace PSB\Core\ErrorHandling\ErrorLastResort;


use PSB\Core\DateTimeConverter;
use PSB\Core\ErrorHandling\ErrorLastResort\Pipeline\MoveErrorsToErrorQueuePipelineStep;
use PSB\Core\Feature\Feature;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\StageContext\DispatchContext;
use PSB\Core\Pipeline\PipelineFactory;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Transport\QueueBindings;
use PSB\Core\Util\Clock\ClockInterface;
use PSB\Core\Util\Settings;

class ErrorLastResortFeature extends Feature
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
            "Send only endpoints can't be used to forward received messages to the error queue as the endpoint requires receive capabilities."
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
    public function setup(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $errorQueue = $settings->get(KnownSettingsEnum::ERROR_QUEUE);
        /** @var QueueBindings $queueBindings */
        $queueBindings = $settings->get(QueueBindings::class);
        $queueBindings->bindSending($errorQueue);

        $localAddress = $settings->get(KnownSettingsEnum::LOCAL_ADDRESS);

        $builder->defineSingleton(
            ExceptionToHeadersConverter::class,
            function () use ($builder) {
                return new ExceptionToHeadersConverter(
                    $builder->build(ClockInterface::class),
                    $builder->build(DateTimeConverter::class)
                );
            }
        );

        $registration = $pipelineModifications->registerStep(
            'MoveErrorsToErrorQueuePipelineStep',
            MoveErrorsToErrorQueuePipelineStep::class,
            function () use ($errorQueue, $localAddress, $builder) {
                /** @var PipelineFactory $pipelineFactory */
                $pipelineFactory = $builder->build(PipelineFactory::class);
                return new MoveErrorsToErrorQueuePipelineStep(
                    $errorQueue,
                    $localAddress,
                    $pipelineFactory->createStartingWith(
                        DispatchContext::class,
                        $builder->build(PipelineModifications::class)
                    ),
                    $builder->build(ExceptionToHeadersConverter::class),
                    $builder->build(OutgoingContextFactory::class)
                );
            }
        );
        $registration->insertBeforeIfExists('FirstLevelRetryPipelineStep');
        $registration->insertBeforeIfExists('SecondLevelRetryPipelineStep');
    }
}
