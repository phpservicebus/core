<?php
namespace PSB\Core\MessageMutation\Pipeline\Incoming;


interface IncomingPhysicalMessageMutatorInterface
{
    public function mutateIncoming(IncomingPhysicalMessageMutationContext $context);
}
