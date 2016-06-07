<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\EndpointControlToken;
use PSB\Core\Transport\PushContext;
use PSB\Core\Transport\ReceiveCancellationToken;

/**
 * @mixin PushContext
 */
class PushContextSpec extends ObjectBehavior
{
    /** @var ReceiveCancellationToken */
    private $cancellationTokenMock;

    /** @var ReceiveCancellationToken */
    private $endpointControlTokenMock;

    function let(ReceiveCancellationToken $cancellationToken, EndpointControlToken $endpointControlToken)
    {
        $this->cancellationTokenMock = $cancellationToken;
        $this->endpointControlTokenMock = $endpointControlToken;
        $this->beConstructedWith('id', ['some' => 'header'], 'body', $cancellationToken, $endpointControlToken);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\PushContext');
    }

    function it_contains_the_id_set_at_construction()
    {
        $this->getMessageId()->shouldReturn('id');
    }

    function it_contains_the_headers_set_at_construction()
    {
        $this->getHeaders()->shouldReturn(['some' => 'header']);
    }

    function it_contains_the_body_set_at_construction()
    {
        $this->getBody()->shouldReturn('body');
    }

    function it_contains_the_cancellation_token_set_at_construction()
    {
        $this->getCancellationToken()->shouldReturn($this->cancellationTokenMock);
    }

    function it_contains_the_endpoint_token_set_at_construction()
    {
        $this->getEndpointControlToken()->shouldReturn($this->endpointControlTokenMock);
    }

    function it_throws_if_id_is_empty_on_construction()
    {
        $this->beConstructedWith('', ['some' => 'header'], 'body', $this->cancellationTokenMock);
        $this->shouldThrow()->duringInstantiation();
    }

    function it_throws_if_id_is_null_on_construction()
    {
        $this->beConstructedWith(null, ['some' => 'header'], 'body', $this->cancellationTokenMock);
        $this->shouldThrow()->duringInstantiation();
    }

    function it_throws_if_headers_are_empty_on_construction()
    {
        $this->beConstructedWith('id', [], 'body', $this->cancellationTokenMock);
        $this->shouldThrow()->duringInstantiation();
    }

    function it_throws_if_body_is_empty_on_construction()
    {
        $this->beConstructedWith('id', ['some' => 'header'], '', $this->cancellationTokenMock);
        $this->shouldThrow()->duringInstantiation();
    }
}
