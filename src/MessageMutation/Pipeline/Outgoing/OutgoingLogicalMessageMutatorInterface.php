<?php
namespace PSB\Core\MessageMutation\Pipeline\Outgoing;


interface OutgoingLogicalMessageMutatorInterface
{
    public function mutateOutgoing(OutgoingLogicalMessageMutationContext $context);
}
