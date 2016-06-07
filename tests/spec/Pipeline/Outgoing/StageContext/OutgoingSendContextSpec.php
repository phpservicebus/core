<?php

namespace spec\PSB\Core\Pipeline\Outgoing\StageContext;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessage;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingSendContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\SendOptions;
use PSB\Core\Transport\IncomingPhysicalMessage;

/**
 * @mixin OutgoingSendContext
 */
class OutgoingSendContextSpec extends ObjectBehavior
{
    private $sendOptionsMock;

    /**
     * @var OutgoingLogicalMessage
     */
    private $messageMock;
    private $transportOperationsMock;
    private $incomingPhysicalMessageMock;

    function let(
        OutgoingLogicalMessage $message,
        PendingTransportOperations $transportOperations,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PipelineStageContext $parentContext
    ) {
        $this->sendOptionsMock = new SendOptions();
        $this->sendOptionsMock->setMessageId('someid');
        $this->messageMock = $message;
        $this->incomingPhysicalMessageMock = $incomingPhysicalMessage;
        $this->transportOperationsMock = $transportOperations;
        $this->beConstructedWith(
            $message,
            $this->sendOptionsMock,
            $incomingPhysicalMessage,
            $transportOperations,
            $parentContext
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Outgoing\StageContext\OutgoingSendContext');
    }

    function it_contains_the_logical_message_set_at_construction()
    {
        $this->getLogicalMessage()->shouldReturn($this->messageMock);
    }

    function it_provides_the_logical_message_class()
    {
        $this->messageMock->getMessageClass()->willReturn('irelevant');
        $this->getMessageClass()->shouldReturn('irelevant');
    }

    function it_contains_the_publish_options_set_at_construction()
    {
        $this->getSendOptions()->shouldReturn($this->sendOptionsMock);
    }

    function it_contains_the_incoming_physical_message_set_at_construction()
    {
        $this->getIncomingPhysicalMessage()->shouldReturn($this->incomingPhysicalMessageMock);
    }

    function it_contains_the_transport_operations_set_at_construction()
    {
        $this->getPendingTransportOperations()->shouldReturn($this->transportOperationsMock);
    }

    function it_contains_the_message_id_set_at_construction()
    {
        $this->getMessageId()->shouldReturn('someid');
    }

    function it_contains_the_headers_set_at_construction()
    {
        $this->getHeaders()->shouldReturn([]);
    }
}
