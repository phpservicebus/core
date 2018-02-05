<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;

use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Pipeline\PipelineInterface;
use PSB\Core\Transport\PushContext;
use PSB\Core\Transport\PushPipe;
use PSB\Core\Transport\TransportReceiveContextFactory;

/**
 * @mixin PushPipe
 */
class PushPipeSpec extends ObjectBehavior
{
    function it_is_initializable(TransportReceiveContextFactory $contextFactory, PipelineInterface $pipeline)
    {
        $this->beConstructedWith($contextFactory, $pipeline);
        $this->shouldHaveType('PSB\Core\Transport\PushPipe');
    }

    function it_pushes_by_invoking_the_pipeline(
        TransportReceiveContextFactory $contextFactory,
        PipelineInterface $pipeline,
        PushContext $pushContext,
        TransportReceiveContext $transportReceiveContext
    ) {
        $this->beConstructedWith($contextFactory, $pipeline);
        $contextFactory->createFromPushContext($pushContext)->willReturn($transportReceiveContext);

        $pipeline->invoke($transportReceiveContext)->shouldBeCalled();

        $this->push($pushContext);
    }
}
