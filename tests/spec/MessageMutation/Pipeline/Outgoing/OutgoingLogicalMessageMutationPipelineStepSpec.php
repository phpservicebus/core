<?php

namespace spec\PSB\Core\MessageMutation\Pipeline\Outgoing;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutationContext;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutationPipelineStep;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutatorInterface;
use PSB\Core\MessageMutatorRegistry;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessage;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use spec\PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutationPipelineStepSpec\Mutator;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin OutgoingLogicalMessageMutationPipelineStep
 */
class OutgoingLogicalMessageMutationPipelineStepSpec extends ObjectBehavior
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
        $this->shouldHaveType('PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutationPipelineStep');
    }

    function it_calls_the_next_step_in_the_pipeline_if_no_mutators_are_registered(
        OutgoingLogicalMessageContext $context,
        SimpleCallable $next
    ) {
        $this->mutatorRegistryMock->getOutgoingLogicalMessageMutatorIds()->willReturn([]);

        $next->__invoke()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_invokes_all_registered_mutators_and_does_not_update_the_message_if_there_are_no_changes(
        OutgoingLogicalMessageContext $context,
        SimpleCallable $next,
        OutgoingLogicalMessage $logicalMessage,
        BuilderInterface $builder,
        OutgoingLogicalMessageMutatorInterface $mutator1,
        OutgoingLogicalMessageMutatorInterface $mutator2
    ) {
        $headers = ['some' => 'header'];
        $this->mutatorRegistryMock->getOutgoingLogicalMessageMutatorIds()->willReturn(['1', '2']);
        $logicalMessage->getMessageInstance()->willReturn((object)['message']);
        $context->getMessage()->willReturn($logicalMessage);
        $context->getHeaders()->willReturn($headers);
        $context->getBuilder()->willReturn($builder);
        $builder->build('1')->willReturn($mutator1);
        $builder->build('2')->willReturn($mutator2);

        $mutator1->mutateOutgoing(Argument::type(OutgoingLogicalMessageMutationContext::class))->shouldBeCalled();
        $mutator2->mutateOutgoing(Argument::type(OutgoingLogicalMessageMutationContext::class))->shouldBeCalled();
        $logicalMessage->updateInstance(Argument::any())->shouldNotBeCalled();
        $context->replaceHeaders($headers)->shouldBeCalled();
        $next->__invoke()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_invokes_all_registered_mutators_and_updates_the_message_if_there_are_changes(
        OutgoingLogicalMessageContext $context,
        SimpleCallable $next,
        OutgoingLogicalMessage $logicalMessage,
        BuilderInterface $builder
    ) {
        $headers = ['some' => 'header'];
        $this->mutatorRegistryMock->getOutgoingLogicalMessageMutatorIds()->willReturn(['1']);
        $logicalMessage->getMessageInstance()->willReturn((object)['message']);
        $context->getMessage()->willReturn($logicalMessage);
        $context->getHeaders()->willReturn($headers);
        $context->getBuilder()->willReturn($builder);
        $builder->build('1')->willReturn(new Mutator());

        $logicalMessage->updateInstance(Argument::any())->shouldBeCalled();
        $context->replaceHeaders(array_merge($headers, ['new' => 'header']))->shouldBeCalled();
        $next->__invoke()->shouldBeCalled();

        $this->invoke($context, $next);

    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(OutgoingLogicalMessageContext::class);
    }
}

namespace spec\PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutationPipelineStepSpec;

use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutationContext;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutatorInterface;

class Mutator implements OutgoingLogicalMessageMutatorInterface
{
    public function mutateOutgoing(OutgoingLogicalMessageMutationContext $context)
    {
        $context->updateMessage((object)['newmessage']);
        $context->setHeader('new', 'header');
    }
}
