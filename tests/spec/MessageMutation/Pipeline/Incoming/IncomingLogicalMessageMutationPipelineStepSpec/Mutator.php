<?php

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
