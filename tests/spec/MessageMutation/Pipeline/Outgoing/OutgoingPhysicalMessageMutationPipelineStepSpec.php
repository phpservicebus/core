<?php

namespace spec\PSB\Core\MessageMutation\Pipeline\Outgoing;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingPhysicalMessageMutationContext;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingPhysicalMessageMutationPipelineStep;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingPhysicalMessageMutatorInterface;
use PSB\Core\MessageMutatorRegistry;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPhysicalMessageContext;
use PSB\Core\Transport\OutgoingPhysicalMessage;
use spec\PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingPhysicalMessageMutationPipelineStepSpec\Mutator;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin OutgoingPhysicalMessageMutationPipelineStep
 */
class OutgoingPhysicalMessageMutationPipelineStepSpec extends ObjectBehavior
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
        $this->shouldHaveType('PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingPhysicalMessageMutationPipelineStep');
    }

    function it_calls_the_next_middleware_in_the_pipeline_if_there_are_no_mutators_registered(
        OutgoingPhysicalMessageContext $context,
        SimpleCallable $next
    ) {
        $this->mutatorRegistryMock->getOutgoingPhysicalMessageMutatorIds()->willReturn([]);

        $next->__invoke()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_invokes_all_registered_mutators(
        OutgoingPhysicalMessageContext $context,
        SimpleCallable $next,
        OutgoingPhysicalMessage $physicalMessage,
        BuilderInterface $builder,
        OutgoingPhysicalMessageMutatorInterface $mutator1,
        OutgoingPhysicalMessageMutatorInterface $mutator2
    ) {
        $context->getMessage()->willReturn($physicalMessage);
        $physicalMessage->getBody()->willReturn('body');
        $physicalMessage->getHeaders()->willReturn(['some' => 'headers']);
        $this->mutatorRegistryMock->getOutgoingPhysicalMessageMutatorIds()->willReturn(['1', '2']);
        $context->getBuilder()->willReturn($builder);
        $builder->build('1')->willReturn($mutator1);
        $builder->build('2')->willReturn($mutator2);

        $mutator1->mutateOutgoing(Argument::type(OutgoingPhysicalMessageMutationContext::class))->shouldBeCalled();
        $mutator2->mutateOutgoing(Argument::type(OutgoingPhysicalMessageMutationContext::class))->shouldBeCalled();
        $physicalMessage->replaceBody('body')->shouldBeCalled();
        $physicalMessage->replaceHeaders(['some' => 'headers'])->shouldBeCalled();
        $next->__invoke()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(OutgoingPhysicalMessageContext::class);
    }
}
