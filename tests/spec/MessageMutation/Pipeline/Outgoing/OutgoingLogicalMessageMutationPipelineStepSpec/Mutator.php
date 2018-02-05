<?php

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
