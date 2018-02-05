<?php

namespace spec\PSB\Core;

use PhpSpec\ObjectBehavior;

use PSB\Core\MessageMutatorRegistry;

/**
 * @mixin MessageMutatorRegistry
 */
class MessageMutatorRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\MessageMutatorRegistry');
    }

    function it_registers_incoming_logical_messages()
    {
        $mutatorContainerId1 = 'irrelevant1';
        $mutatorContainerId2 = 'irrelevant2';
        $this->registerIncomingLogicalMessageMutator($mutatorContainerId1);
        $this->registerIncomingLogicalMessageMutator($mutatorContainerId2);
        $this->getIncomingLogicalMessageMutatorIds()->shouldReturn([$mutatorContainerId1, $mutatorContainerId2]);
    }

    function it_registers_duplicate_incoming_logical_messages_only_once()
    {
        $mutatorContainerId = 'irrelevant';
        $this->registerIncomingLogicalMessageMutator($mutatorContainerId);
        $this->registerIncomingLogicalMessageMutator($mutatorContainerId);
        $this->getIncomingLogicalMessageMutatorIds()->shouldReturn([$mutatorContainerId]);
    }

    function it_registers_incoming_physical_messages()
    {
        $mutatorContainerId1 = 'irrelevant1';
        $mutatorContainerId2 = 'irrelevant2';
        $this->registerIncomingPhysicalMessageMutator($mutatorContainerId1);
        $this->registerIncomingPhysicalMessageMutator($mutatorContainerId2);
        $this->getIncomingPhysicalMessageMutatorIds()->shouldReturn([$mutatorContainerId1, $mutatorContainerId2]);
    }

    function it_registers_duplicate_incoming_physical_messages_only_once()
    {
        $mutatorContainerId = 'irrelevant';
        $this->registerIncomingPhysicalMessageMutator($mutatorContainerId);
        $this->registerIncomingPhysicalMessageMutator($mutatorContainerId);
        $this->getIncomingPhysicalMessageMutatorIds()->shouldReturn([$mutatorContainerId]);
    }

    function it_registers_outgoing_logical_messages()
    {
        $mutatorContainerId1 = 'irrelevant1';
        $mutatorContainerId2 = 'irrelevant2';
        $this->registerOutgoingLogicalMessageMutator($mutatorContainerId1);
        $this->registerOutgoingLogicalMessageMutator($mutatorContainerId2);
        $this->getOutgoingLogicalMessageMutatorIds()->shouldReturn([$mutatorContainerId1, $mutatorContainerId2]);
    }

    function it_registers_duplicate_outgoing_logical_messages_only_once()
    {
        $mutatorContainerId = 'irrelevant';
        $this->registerOutgoingLogicalMessageMutator($mutatorContainerId);
        $this->registerOutgoingLogicalMessageMutator($mutatorContainerId);
        $this->getOutgoingLogicalMessageMutatorIds()->shouldReturn([$mutatorContainerId]);
    }

    function it_registers_outgoing_physical_messages()
    {
        $mutatorContainerId1 = 'irrelevant1';
        $mutatorContainerId2 = 'irrelevant2';
        $this->registerOutgoingPhysicalMessageMutator($mutatorContainerId1);
        $this->registerOutgoingPhysicalMessageMutator($mutatorContainerId2);
        $this->getOutgoingPhysicalMessageMutatorIds()->shouldReturn([$mutatorContainerId1, $mutatorContainerId2]);
    }

    function it_registers_duplicate_outgoing_physical_messages_only_once()
    {
        $mutatorContainerId = 'irrelevant';
        $this->registerOutgoingPhysicalMessageMutator($mutatorContainerId);
        $this->registerOutgoingPhysicalMessageMutator($mutatorContainerId);
        $this->getOutgoingPhysicalMessageMutatorIds()->shouldReturn([$mutatorContainerId]);
    }
}
