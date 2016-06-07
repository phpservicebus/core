<?php

namespace spec\PSB\Core\Pipeline\Outgoing\StageContext;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessage;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\Routing\MulticastAddressTag;
use PSB\Core\Transport\IncomingPhysicalMessage;

/**
 * @mixin OutgoingLogicalMessageContext
 */
class OutgoingLogicalMessageContextSpec extends ObjectBehavior
{
    private $logicalMessageMock;
    private $addressTagsMock;
    private $transportOperationsMock;
    private $incomingPhysicalMessageMock;

    function let(
        OutgoingLogicalMessage $logicalMessage,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $pendingTransportOperations,
        PipelineStageContext $parentContext
    ) {
        $this->logicalMessageMock = $logicalMessage;
        $this->addressTagsMock = [new MulticastAddressTag('MessageType')];
        $this->transportOperationsMock = $pendingTransportOperations;
        $this->incomingPhysicalMessageMock = $incomingPhysicalMessage;
        $this->beConstructedWith(
            'id',
            [],
            $logicalMessage,
            $this->addressTagsMock,
            false,
            $incomingPhysicalMessage,
            $pendingTransportOperations,
            $parentContext
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext');
    }

    function it_contains_the_message_id_set_at_construction()
    {
        $this->getMessageId()->shouldReturn('id');
    }

    function it_contains_the_headers_set_at_construction()
    {
        $this->getHeaders()->shouldReturn([]);
    }

    function it_contains_the_message_set_at_construction()
    {
        $this->getMessage()->shouldReturn($this->logicalMessageMock);
    }

    function it_contains_the_address_tags_at_construction()
    {
        $this->getAddressTags()->shouldReturn($this->addressTagsMock);
    }

    function it_contains_the_immediate_dispatch_set_at_construction()
    {
        $this->isImmediateDispatchEnabled()->shouldReturn(false);
    }

    function it_contains_the_physical_message_set_at_construction()
    {
        $this->getIncomingPhysicalMessage()->shouldReturn($this->incomingPhysicalMessageMock);
    }

    function it_contains_the_transport_operations_set_at_construction()
    {
        $this->getPendingTransportOperations()->shouldReturn($this->transportOperationsMock);
    }
}
