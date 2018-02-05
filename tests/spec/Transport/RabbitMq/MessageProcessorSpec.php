<?php

namespace spec\PSB\Core\Transport\RabbitMq;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\EndpointControlToken;
use PSB\Core\Exception\CriticalErrorException;
use PSB\Core\Transport\PushContext;
use PSB\Core\Transport\PushPipe;
use PSB\Core\Transport\RabbitMq\BrokerModel;
use PSB\Core\Transport\RabbitMq\MessageConverter;
use PSB\Core\Transport\RabbitMq\MessageProcessor;
use PSB\Core\Transport\RabbitMq\RoutingTopology;
use PSB\Core\Transport\ReceiveCancellationToken;

/**
 * @mixin MessageProcessor
 */
class MessageProcessorSpec extends ObjectBehavior
{
    /**
     * @var BrokerModel
     */
    private $brokerModelMock;

    /**
     * @var RoutingTopology
     */
    private $routingTopologyMock;

    /**
     * @var MessageConverter
     */
    private $messageConverterMock;

    function let(
        BrokerModel $brokerModel,
        RoutingTopology $routingTopology,
        MessageConverter $messageConverter
    ) {
        $this->brokerModelMock = $brokerModel;
        $this->routingTopologyMock = $routingTopology;
        $this->messageConverterMock = $messageConverter;
        $this->beConstructedWith($brokerModel, $routingTopology, $messageConverter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\RabbitMq\MessageProcessor');
    }

    function it_rethrows_if_critical_error_exception_occurs_during_next_invoke(
        \AMQPEnvelope $envelope,
        \AMQPQueue $queue,
        PushPipe $pushPipe,
        ReceiveCancellationToken $cancellationToken,
        EndpointControlToken $endpointControlToken
    ) {
        $this->messageConverterMock->retrieveMessageId($envelope)->willReturn('someid');
        $this->messageConverterMock->retrieveHeaders($envelope)->willReturn(['some' => 'headers']);
        $envelope->getBody()->willReturn('somebody');
        $pushPipe->push(Argument::any())->shouldBeCalled()->willThrow(new CriticalErrorException('whatever'));

        $this->shouldThrow(CriticalErrorException::class)->duringProcess(
            $envelope,
            $queue,
            $pushPipe,
            'irrelevant queue',
            $cancellationToken,
            $endpointControlToken
        );
    }

    function it_requeues_the_message_if_an_exception_occurs(
        \AMQPEnvelope $envelope,
        \AMQPQueue $queue,
        PushPipe $pushPipe,
        ReceiveCancellationToken $cancellationToken,
        EndpointControlToken $endpointControlToken
    ) {
        $this->messageConverterMock->retrieveMessageId($envelope)->willReturn('someid');
        $this->messageConverterMock->retrieveHeaders($envelope)->willReturn(['some' => 'headers']);
        $envelope->getBody()->willReturn('somebody');
        $envelope->getDeliveryTag()->willReturn('irrelevant tag');
        $pushPipe->push(Argument::any())->shouldBeCalled()->willThrow('\Exception');

        $queue->reject('irrelevant tag', AMQP_REQUEUE)->shouldBeCalled();

        $this->process(
            $envelope,
            $queue,
            $pushPipe,
            'irrelevant queue',
            $cancellationToken,
            $endpointControlToken
        )->shouldBe(true);
    }

    function it_forwards_to_error_queue_if_message_has_no_id(
        \AMQPEnvelope $envelope,
        \AMQPQueue $queue,
        PushPipe $pushPipe,
        ReceiveCancellationToken $cancellationToken,
        EndpointControlToken $endpointControlToken
    ) {
        $this->messageConverterMock->retrieveMessageId($envelope)->willThrow('\Exception');
        $envelope->getBody()->willReturn('irrelevant body');
        $envelope->getHeaders()->willReturn([]);
        $envelope->getDeliveryTag()->willReturn('irrelevant tag');

        $this->routingTopologyMock->sendToQueue(
            $this->brokerModelMock,
            'irrelevant error queue',
            'irrelevant body',
            ['headers' => []]
        )->shouldBeCalled();

        $queue->ack('irrelevant tag')->shouldBeCalled();

        $this->process(
            $envelope,
            $queue,
            $pushPipe,
            'irrelevant error queue',
            $cancellationToken,
            $endpointControlToken
        )->shouldBe(true);
    }

    function it_pushes_the_message_through_the_pipe_and_removes_it_from_the_queue_if_no_cancellation_requested(
        \AMQPEnvelope $envelope,
        \AMQPQueue $queue,
        PushPipe $pushPipe,
        ReceiveCancellationToken $cancellationToken,
        EndpointControlToken $endpointControlToken
    ) {
        $this->messageConverterMock->retrieveMessageId($envelope)->willReturn('someid');
        $this->messageConverterMock->retrieveHeaders($envelope)->willReturn(['some' => 'headers']);
        $envelope->getBody()->willReturn('irrelevant body');
        $envelope->getDeliveryTag()->willReturn('irrelevant tag');
        $cancellationToken->isCancellationRequested()->willReturn(false);

        $pushPipe->push(
            new PushContext(
                'someid',
                ['some' => 'headers'],
                'irrelevant body',
                $cancellationToken->getWrappedObject(),
                $endpointControlToken->getWrappedObject()
            )
        )->shouldBeCalled();
        $queue->ack('irrelevant tag')->shouldBeCalled();

        $this->process(
            $envelope,
            $queue,
            $pushPipe,
            'irrelevant error queue',
            $cancellationToken,
            $endpointControlToken
        )->shouldBe(true);
    }

    function it_pushes_the_message_through_the_pipe_and_requeues_it_if_pipe_requested_cancellation(
        \AMQPEnvelope $envelope,
        \AMQPQueue $queue,
        PushPipe $pushPipe,
        ReceiveCancellationToken $cancellationToken,
        EndpointControlToken $endpointControlToken
    ) {
        $this->messageConverterMock->retrieveMessageId($envelope)->willReturn('someid');
        $this->messageConverterMock->retrieveHeaders($envelope)->willReturn(['some' => 'headers']);
        $envelope->getBody()->willReturn('irrelevant body');
        $envelope->getDeliveryTag()->willReturn('irrelevant tag');
        $cancellationToken->isCancellationRequested()->willReturn(true);

        $pushPipe->push(
            new PushContext(
                'someid',
                ['some' => 'headers'],
                'irrelevant body',
                $cancellationToken->getWrappedObject(),
                $endpointControlToken->getWrappedObject()
            )
        )->shouldBeCalled();
        $queue->reject('irrelevant tag', AMQP_REQUEUE)->shouldBeCalled();

        $this->process(
            $envelope,
            $queue,
            $pushPipe,
            'irrelevant error queue',
            $cancellationToken,
            $endpointControlToken
        )->shouldBe(true);
    }

    function it_returns_false_when_endpoint_shutdown_has_been_requested(
        \AMQPEnvelope $envelope,
        \AMQPQueue $queue,
        PushPipe $pushPipe,
        ReceiveCancellationToken $cancellationToken,
        EndpointControlToken $endpointControlToken
    ) {
        $this->messageConverterMock->retrieveMessageId($envelope)->willReturn('someid');
        $this->messageConverterMock->retrieveHeaders($envelope)->willReturn(['some' => 'headers']);
        $endpointControlToken->isShutdownRequested()->willReturn(true);

        $this->process(
            $envelope,
            $queue,
            $pushPipe,
            'irrelevant error queue',
            $cancellationToken,
            $endpointControlToken
        )->shouldBe(false);
    }
}
