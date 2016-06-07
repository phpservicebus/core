<?php
namespace PSB\Core;


class MessageMutatorRegistry
{
    const INCOMING_LOGICAL = 0;
    const INCOMING_PHYSICAL = 1;
    const OUTGOING_LOGICAL = 2;
    const OUTGOING_PHYSICAL = 3;

    private $mutators = [
        self::INCOMING_LOGICAL => [],
        self::INCOMING_PHYSICAL => [],
        self::OUTGOING_LOGICAL => [],
        self::OUTGOING_PHYSICAL => [],
    ];

    /**
     * @param string $mutatorContainerId
     */
    public function registerIncomingLogicalMessageMutator($mutatorContainerId)
    {
        $this->mutators[self::INCOMING_LOGICAL][] = $mutatorContainerId;
    }

    /**
     * @param string $mutatorContainerId
     */
    public function registerIncomingPhysicalMessageMutator($mutatorContainerId)
    {
        $this->mutators[self::INCOMING_PHYSICAL][] = $mutatorContainerId;
    }

    /**
     * @param string $mutatorContainerId
     */
    public function registerOutgoingLogicalMessageMutator($mutatorContainerId)
    {
        $this->mutators[self::OUTGOING_LOGICAL][] = $mutatorContainerId;
    }

    /**
     * @param string $mutatorContainerId
     */
    public function registerOutgoingPhysicalMessageMutator($mutatorContainerId)
    {
        $this->mutators[self::OUTGOING_PHYSICAL][] = $mutatorContainerId;
    }

    /**
     * @return array
     */
    public function getIncomingLogicalMessageMutatorIds()
    {
        return array_unique($this->mutators[self::INCOMING_LOGICAL]);
    }

    /**
     * @return array
     */
    public function getIncomingPhysicalMessageMutatorIds()
    {
        return array_unique($this->mutators[self::INCOMING_PHYSICAL]);
    }

    /**
     * @return array
     */
    public function getOutgoingLogicalMessageMutatorIds()
    {
        return array_unique($this->mutators[self::OUTGOING_LOGICAL]);
    }

    /**
     * @return array
     */
    public function getOutgoingPhysicalMessageMutatorIds()
    {
        return array_unique($this->mutators[self::OUTGOING_PHYSICAL]);
    }
}
