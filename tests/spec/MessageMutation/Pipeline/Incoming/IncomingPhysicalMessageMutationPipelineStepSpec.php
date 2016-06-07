<?php

namespace spec\PSB\Core\MessageMutation\Pipeline\Incoming;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingPhysicalMessageMutationContext;
use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingPhysicalMessageMutationPipelineStep;
use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingPhysicalMessageMutatorInterface;
use PSB\Core\MessageMutatorRegistry;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingPhysicalMessageContext;
use PSB\Core\Transport\IncomingPhysicalMessage;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin IncomingPhysicalMessageMutationPipelineStep
 */
class IncomingPhysicalMessageMutationPipelineStepSpec extends ObjectBehavior
{
    /**
     * @var MessageMutatorRegistry
     */
    private $mutatorRegistryMock;

    function let(MessageMutatorRegistry $mutatorRegistry)
    {
        $this->mutatorRegistryMock = $mutatorRegistry;
        $this->beConstructedWith($mutatorRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\MessageMutation\Pipeline\Incoming\IncomingPhysicalMessageMutationPipelineStep');
    }

    function it_calls_the_next_middleware_in_the_pipeline_if_no_mutators_are_registered(
        IncomingPhysicalMessageContext $context,
        SimpleCallable $next
    ) {
        $this->mutatorRegistryMock->getIncomingPhysicalMessageMutatorIds()->willReturn([]);

        $next->__invoke()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_invokes_all_registered_mutators_and_updates_the_message_and_invokes_next(
        IncomingPhysicalMessageContext $context,
        SimpleCallable $next,
        IncomingPhysicalMessage $physicalMessage,
        BuilderInterface $builder,
        IncomingPhysicalMessageMutatorInterface $mutator1,
        IncomingPhysicalMessageMutatorInterface $mutator2
    ) {
        $context->getMessage()->willReturn($physicalMessage);
        $physicalMessage->getBody()->willReturn('body');
        $physicalMessage->getHeaders()->willReturn(['some' => 'headers']);
        $this->mutatorRegistryMock->getIncomingPhysicalMessageMutatorIds()->willReturn(['1', '2']);
        $context->getBuilder()->willReturn($builder);
        $builder->build('1')->willReturn($mutator1);
        $builder->build('2')->willReturn($mutator2);

        $mutator1->mutateIncoming(Argument::type(IncomingPhysicalMessageMutationContext::class))->shouldBeCalled();
        $mutator2->mutateIncoming(Argument::type(IncomingPhysicalMessageMutationContext::class))->shouldBeCalled();
        $physicalMessage->replaceBody('body')->shouldBeCalled();
        $physicalMessage->replaceHeaders(['some' => 'headers'])->shouldBeCalled();
        $next->__invoke()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(IncomingPhysicalMessageContext::class);
    }
}
