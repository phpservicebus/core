<?php

namespace spec\PSB\Core\Pipeline\Incoming\StageContext;

use PhpSpec\ObjectBehavior;

use PSB\Core\EndpointControlToken;
use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\Transport\IncomingPhysicalMessage;
use PSB\Core\Transport\ReceiveCancellationToken;

/**
 * @mixin TransportReceiveContext
 */
class TransportReceiveContextSpec extends ObjectBehavior
{
    /**
     * @var IncomingPhysicalMessage
     */
    private $incomingMessageMock;

    /**
     * @var ReceiveCancellationToken
     */
    private $cancellationTokenMock;

    /**
     * @var EndpointControlToken
     */
    private $endpointControlTokenMock;

    function let(
        IncomingPhysicalMessage $incomingMessage,
        ReceiveCancellationToken $cancellationToken,
        EndpointControlToken $endpointControlToken,
        PipelineStageContext $parent
    ) {
        $this->incomingMessageMock = $incomingMessage;
        $this->cancellationTokenMock = $cancellationToken;
        $this->endpointControlTokenMock = $endpointControlToken;
        $this->beConstructedWith('id', $incomingMessage, $cancellationToken, $endpointControlToken, $parent);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext');
    }

    function it_contains_the_message_set_at_construction()
    {
        $this->getMessage()->shouldReturn($this->incomingMessageMock);
    }

    function it_contains_the_message_id_from_the_parent_context()
    {
        $this->getMessageId()->shouldReturn('id');
    }

    function it_aborts_the_receive_operation_by_cancelling_the_token()
    {
        $this->cancellationTokenMock->cancel()->shouldBeCalled();

        $this->abortReceiveOperation();
    }

    function it_contains_the_endpoint_token_set_at_construction()
    {
        $this->getEndpointControlToken()->shouldReturn($this->endpointControlTokenMock);
    }
}
