<?php
namespace PSB\Core\MessageMutation\Pipeline\Outgoing;


interface OutgoingPhysicalMessageMutatorInterface
{
    public function mutateOutgoing(OutgoingPhysicalMessageMutationContext $context);
}
