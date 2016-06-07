<?php
namespace PSB\Core\MessageMutation\Pipeline\Incoming;


interface IncomingLogicalMessageMutatorInterface
{
    public function mutateIncoming(IncomingLogicalMessageMutationContext $context);
}
