<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\EndpointControlToken;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Pipeline\PipelineRootStageContext;
use PSB\Core\Transport\IncomingPhysicalMessage;
use PSB\Core\Transport\PushContext;
use PSB\Core\Transport\ReceiveCancellationToken;
use PSB\Core\Transport\TransportReceiveContextFactory;

/**
 * @mixin TransportReceiveContextFactory
 */
class TransportReceiveContextFactorySpec extends ObjectBehavior
{
    function it_is_initializable(BuilderInterface $builder)
    {
        $this->beConstructedWith($builder);
        $this->shouldHaveType('PSB\Core\Transport\TransportReceiveContextFactory');
    }

    function it_creates_from_push_context(BuilderInterface $builder, PushContext $pushContext)
    {
        $this->beConstructedWith($builder);
        $cancellationToken = new ReceiveCancellationToken();
        $endpointControlToken = new EndpointControlToken();
        $pushContext->getMessageId()->willReturn('id');
        $pushContext->getHeaders()->willReturn(['some' => 'header']);
        $pushContext->getBody()->willReturn('body');
        $pushContext->getCancellationToken()->willReturn($cancellationToken);
        $pushContext->getEndpointControlToken()->willReturn($endpointControlToken);
        $this->createFromPushContext($pushContext)->shouldBeLike(
            new TransportReceiveContext(
                'id',
                new IncomingPhysicalMessage('id', ['some' => 'header'], 'body'),
                $cancellationToken,
                $endpointControlToken,
                new PipelineRootStageContext($builder->getWrappedObject())
            )
        );
    }
}
