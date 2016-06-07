<?php

namespace spec\PSB\Core\Pipeline\Incoming;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\EndpointControlToken;
use PSB\Core\OutgoingOptionsFactory;
use PSB\Core\Pipeline\BusOperations;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\PublishOptions;
use PSB\Core\ReplyOptions;
use PSB\Core\SendOptions;
use PSB\Core\SubscribeOptions;
use PSB\Core\Transport\IncomingPhysicalMessage;
use PSB\Core\UnsubscribeOptions;
use spec\PSB\Core\Pipeline\Incoming\IncomingContextSpec\SampleIncomingContext;

/**
 * @mixin SampleIncomingContext
 */
class IncomingContextSpec extends ObjectBehavior
{
    private $messageId = 'irrelevant';
    private $headers = ['some' => 'header'];
    /**
     * @var IncomingPhysicalMessage
     */
    private $incomingPhysicalMessageMock;
    /**
     * @var PendingTransportOperations
     */
    private $pendingTransportOperationsMock;
    /**
     * @var BusOperations
     */
    private $busOperationsMock;
    /**
     * @var OutgoingOptionsFactory
     */
    private $outgoingOptionsFactoryMock;
    /**
     * @var EndpointControlToken
     */
    private $endpointControlTokenMock;
    /**
     * @var PipelineStageContext
     */
    private $parentContextMock;

    function let(
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $pendingTransportOperations,
        BusOperations $busOperations,
        OutgoingOptionsFactory $outgoingOptionsFactory,
        EndpointControlToken $endpointControlToken,
        PipelineStageContext $parentContext
    ) {
        $this->incomingPhysicalMessageMock = $incomingPhysicalMessage;
        $this->pendingTransportOperationsMock = $pendingTransportOperations;
        $this->busOperationsMock = $busOperations;
        $this->outgoingOptionsFactoryMock = $outgoingOptionsFactory;
        $this->endpointControlTokenMock = $endpointControlToken;
        $this->parentContextMock = $parentContext;

        $this->beAnInstanceOf(SampleIncomingContext::class);
        $this->beConstructedWith(
            $this->messageId,
            $this->headers,
            $incomingPhysicalMessage,
            $pendingTransportOperations,
            $busOperations,
            $outgoingOptionsFactory,
            $endpointControlToken,
            $parentContext
        );
    }

    function it_sends_a_message_with_default_options_if_none_provided($message, SendOptions $options)
    {
        $this->outgoingOptionsFactoryMock->createSendOptions()->willReturn($options);
        $this->busOperationsMock->send($message, $options, $this)->shouldBeCalled();

        $this->send($message);
    }

    function it_sends_a_message_with_provided_options($message, SendOptions $options)
    {
        $this->busOperationsMock->send($message, $options, $this)->shouldBeCalled();

        $this->send($message, $options);
    }

    function it_sends_locally_a_message_with_default_options_if_none_provided($message, SendOptions $options)
    {
        $this->outgoingOptionsFactoryMock->createSendOptions()->willReturn($options);

        $options->routeToLocalEndpointInstance()->shouldBeCalled();
        $this->busOperationsMock->send($message, $options, $this)->shouldBeCalled();

        $this->sendLocal($message);
    }

    function it_sends_locally_a_message_with_provided_options($message, SendOptions $options)
    {
        $options->routeToLocalEndpointInstance()->shouldBeCalled();
        $this->busOperationsMock->send($message, $options, $this)->shouldBeCalled();

        $this->sendLocal($message, $options);
    }

    function it_publishes_a_message_with_default_options_if_none_provided($message, PublishOptions $options)
    {
        $this->outgoingOptionsFactoryMock->createPublishOptions()->willReturn($options);
        $this->busOperationsMock->publish($message, $options, $this)->shouldBeCalled();

        $this->publish($message);
    }

    function it_publishes_a_message_with_provided_options($message, PublishOptions $options)
    {
        $this->busOperationsMock->publish($message, $options, $this)->shouldBeCalled();

        $this->publish($message, $options);
    }

    function it_subscribes_to_a_message_with_default_options_if_none_provided($message, SubscribeOptions $options)
    {
        $this->outgoingOptionsFactoryMock->createSubscribeOptions()->willReturn($options);
        $this->busOperationsMock->subscribe($message, $options, $this)->shouldBeCalled();

        $this->subscribe($message);
    }

    function it_subscribes_to_a_message_with_provided_options($message, SubscribeOptions $options)
    {
        $this->busOperationsMock->subscribe($message, $options, $this)->shouldBeCalled();

        $this->subscribe($message, $options);
    }

    function it_unsubscribes_from_a_message_with_default_options_if_none_provided($message, UnsubscribeOptions $options)
    {
        $this->outgoingOptionsFactoryMock->createUnsubscribeOptions()->willReturn($options);
        $this->busOperationsMock->unsubscribe($message, $options, $this)->shouldBeCalled();

        $this->unsubscribe($message);
    }

    function it_unsubscribes_from_a_message_with_provided_options($message, UnsubscribeOptions $options)
    {
        $this->busOperationsMock->unsubscribe($message, $options, $this)->shouldBeCalled();

        $this->unsubscribe($message, $options);
    }

    function it_replies_with_a_message_with_default_options_if_none_provided($message, ReplyOptions $options)
    {
        $this->outgoingOptionsFactoryMock->createReplyOptions()->willReturn($options);
        $this->busOperationsMock->reply($message, $options, $this)->shouldBeCalled();

        $this->reply($message);
    }

    function it_replies_with_a_message_with_provided_options($message, ReplyOptions $options)
    {
        $this->busOperationsMock->reply($message, $options, $this)->shouldBeCalled();

        $this->reply($message, $options);
    }

    function it_contains_the_message_id_set_at_construction()
    {
        $this->getMessageId()->shouldReturn($this->messageId);
    }

    function it_contains_the_headers_set_at_construction()
    {
        $this->getHeaders()->shouldReturn($this->headers);
    }

    function it_sets_a_header()
    {
        $this->setHeader('some', 'value');
        $this->getHeaders()->shouldReturn(array_merge($this->headers, ['some' => 'value']));
    }

    function it_replaces_headers()
    {
        $this->replaceHeaders(['some' => 'value']);
        $this->getHeaders()->shouldReturn(['some' => 'value']);
    }

    function it_contains_the_incoming_physical_message_set_at_construction()
    {
        $this->getIncomingPhysicalMessage()->shouldReturn($this->incomingPhysicalMessageMock);
    }

    function it_contains_the_pending_operations_set_at_construction()
    {
        $this->getPendingTransportOperations()->shouldReturn($this->pendingTransportOperationsMock);
    }

    function it_contains_the_endpoint_token_set_at_construction()
    {
        $this->getEndpointControlToken()->shouldReturn($this->endpointControlTokenMock);
    }

    function it_requests_endpoint_shutdown()
    {
        $this->endpointControlTokenMock->requestShutdown()->shouldBeCalled();
        $this->shutdownThisEndpointAfterCurrentMessage();
    }
}

namespace spec\PSB\Core\Pipeline\Incoming\IncomingContextSpec;

use PSB\Core\Pipeline\Incoming\IncomingContext;

class SampleIncomingContext extends IncomingContext
{

}
