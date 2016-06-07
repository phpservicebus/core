<?php

namespace spec\PSB\Core\MessageMutation\Pipeline\Incoming;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingLogicalMessageMutationContext;
use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingLogicalMessageMutationPipelineStep;
use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingLogicalMessageMutatorInterface;
use PSB\Core\MessageMutatorRegistry;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessage;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactory;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingLogicalMessageContext;
use spec\PSB\Core\MessageMutation\Pipeline\Incoming\IncomingLogicalMessageMutationPipelineStepSpec\Mutator;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin IncomingLogicalMessageMutationPipelineStep
 */
class IncomingLogicalMessageMutationPipelineStepSpec extends ObjectBehavior
{
    /**
     * @var MessageMutatorRegistry
     */
    private $mutatorRegistryMock;

    /**
     * @var IncomingLogicalMessageFactory
     */
    private $messageFactoryMock;

    function let(MessageMutatorRegistry $mutatorRegistry, IncomingLogicalMessageFactory $messageFactory)
    {
        $this->mutatorRegistryMock = $mutatorRegistry;
        $this->messageFactoryMock = $messageFactory;
        $this->beConstructedWith($mutatorRegistry, $messageFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\MessageMutation\Pipeline\Incoming\IncomingLogicalMessageMutationPipelineStep');
    }

    function it_calls_the_next_step_in_the_pipeline_if_no_mutators_are_registered(
        IncomingLogicalMessageContext $context,
        SimpleCallable $next
    ) {
        $this->mutatorRegistryMock->getIncomingLogicalMessageMutatorIds()->willReturn([]);

        $next->__invoke()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_invokes_all_registered_mutators_and_does_not_update_the_message_if_there_are_no_changes(
        IncomingLogicalMessageContext $context,
        SimpleCallable $next,
        IncomingLogicalMessage $logicalMessage,
        BuilderInterface $builder,
        IncomingLogicalMessageMutatorInterface $mutator1,
        IncomingLogicalMessageMutatorInterface $mutator2
    ) {
        $headers = ['some' => 'header'];
        $messageInstance = (object)['message'];
        $this->mutatorRegistryMock->getIncomingLogicalMessageMutatorIds()->willReturn(['1', '2']);
        $logicalMessage->getMessageInstance()->willReturn($messageInstance);
        $context->getMessage()->willReturn($logicalMessage);
        $context->getHeaders()->willReturn($headers);
        $context->getBuilder()->willReturn($builder);
        $builder->build('1')->willReturn($mutator1);
        $builder->build('2')->willReturn($mutator2);

        $mutator1->mutateIncoming(Argument::type(IncomingLogicalMessageMutationContext::class))->shouldBeCalled();
        $mutator2->mutateIncoming(Argument::type(IncomingLogicalMessageMutationContext::class))->shouldBeCalled();
        $logicalMessage->updateInstance(Argument::any(), Argument::any())->shouldNotBeCalled();
        $context->replaceHeaders($headers)->shouldBeCalled();
        $next->__invoke()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_invokes_all_registered_mutators_and_updates_the_message_if_there_are_changes(
        IncomingLogicalMessageContext $context,
        SimpleCallable $next,
        IncomingLogicalMessage $logicalMessage,
        BuilderInterface $builder
    ) {
        $headers = ['some' => 'header'];
        $messageInstance = (object)['message'];
        $this->mutatorRegistryMock->getIncomingLogicalMessageMutatorIds()->willReturn(['1']);
        $logicalMessage->getMessageInstance()->willReturn($messageInstance);
        $context->getMessage()->willReturn($logicalMessage);
        $context->getHeaders()->willReturn($headers);
        $context->getBuilder()->willReturn($builder);
        $builder->build('1')->willReturn(new Mutator());

        $logicalMessage->updateInstance(Argument::any(), $this->messageFactoryMock)->shouldBeCalled();
        $context->replaceHeaders(array_merge($headers, ['new' => 'header']))->shouldBeCalled();
        $next->__invoke()->shouldBeCalled();

        $this->invoke($context, $next);
    }

    function it_reports_with_the_correct_stage_context_class()
    {
        self::getStageContextClass()->shouldReturn(IncomingLogicalMessageContext::class);
    }
}

namespace spec\PSB\Core\MessageMutation\Pipeline\Incoming\IncomingLogicalMessageMutationPipelineStepSpec;

use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingLogicalMessageMutationContext;
use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingLogicalMessageMutatorInterface;

class Mutator implements IncomingLogicalMessageMutatorInterface
{
    public function mutateIncoming(IncomingLogicalMessageMutationContext $context)
    {
        $context->updateMessage((object)['newmessage']);
        $context->setHeader('new', 'header');
    }
}
