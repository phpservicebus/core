<?php

namespace spec\PSB\Core\Pipeline\Outgoing\StageContext;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPhysicalMessageContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Routing\MulticastAddressTag;
use PSB\Core\Transport\IncomingPhysicalMessage;
use PSB\Core\Transport\OutgoingPhysicalMessage;

/**
 * @mixin OutgoingPhysicalMessageContext
 */
class OutgoingPhysicalMessageContextSpec extends ObjectBehavior
{
    /**
     * @var OutgoingPhysicalMessage
     */
    private $physicalMessageMock;
    private $addressTagsMock;
    private $transportOperationsMock;

    /**
     * @var IncomingPhysicalMessage
     */
    private $incomingPhysicalMessageMock;

    function let(
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $pendingTransportOperations,
        OutgoingLogicalMessageContext $parentContext
    ) {
        $this->physicalMessageMock = new OutgoingPhysicalMessage('id', [], 'body');
        $this->addressTagsMock = [new MulticastAddressTag('MessageType')];
        $this->transportOperationsMock = $pendingTransportOperations;
        $this->incomingPhysicalMessageMock = $incomingPhysicalMessage;
        $this->beConstructedWith(
            'id',
            [],
            $this->physicalMessageMock,
            $this->addressTagsMock,
            false,
            $incomingPhysicalMessage,
            $pendingTransportOperations,
            $parentContext
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPhysicalMessageContext');
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
        $this->getMessage()->shouldReturn($this->physicalMessageMock);
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

    function it_simultaneously_sets_the_header_of_both_context_and_message()
    {
        $this->setHeader('key', 'value');

        $this->getHeaders()->shouldReturn(['key' => 'value']);
        expect($this->physicalMessageMock->getHeaders())->shouldBe(['key' => 'value']);
    }
}
