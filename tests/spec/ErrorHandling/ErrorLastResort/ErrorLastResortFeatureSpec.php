<?php

namespace spec\PSB\Core\ErrorHandling\ErrorLastResort;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\ErrorHandling\ErrorLastResort\ErrorLastResortFeature;
use PSB\Core\ErrorHandling\ErrorLastResort\ExceptionToHeadersConverter;
use PSB\Core\ErrorHandling\ErrorLastResort\Pipeline\MoveErrorsToErrorQueuePipelineStep;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Pipeline\StepRegistration;
use PSB\Core\Transport\QueueBindings;
use PSB\Core\Util\Settings;

/**
 * @mixin ErrorLastResortFeature
 */
class ErrorLastResortFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\ErrorHandling\ErrorLastResort\ErrorLastResortFeature');
    }

    function it_describes_as_being_enabled_by_default()
    {
        $this->describe();

        $this->isEnabledByDefault()->shouldReturn(true);
    }

    function it_sets_up(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications,
        QueueBindings $queueBindings,
        StepRegistration $registration
    ) {
        $errorQueue = 'irrelevant';
        $settings->get(KnownSettingsEnum::ERROR_QUEUE)->willReturn($errorQueue);
        $settings->get(KnownSettingsEnum::LOCAL_ADDRESS)->willReturn();
        $settings->get(QueueBindings::class)->willReturn($queueBindings);
        $pipelineModifications->registerStep(
            'MoveErrorsToErrorQueuePipelineStep',
            MoveErrorsToErrorQueuePipelineStep::class,
            Argument::type('\Closure')
        )->willReturn($registration);

        $queueBindings->bindSending($errorQueue)->shouldBeCalled();
        $builder->defineSingleton(ExceptionToHeadersConverter::class, Argument::type('\Closure'))->shouldBeCalled();
        $registration->insertBeforeIfExists('FirstLevelRetryPipelineStep')->shouldBeCalled();
        $registration->insertBeforeIfExists('SecondLevelRetryPipelineStep')->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
