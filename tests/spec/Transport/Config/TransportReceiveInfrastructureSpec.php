<?php

namespace spec\PSB\Core\Transport\Config;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Transport\Config\TransportReceiveInfrastructure;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin TransportReceiveInfrastructure
 */
class TransportReceiveInfrastructureSpec extends ObjectBehavior
{
    /**
     * @var SimpleCallable
     */
    private $messagePusherFactoryMock;

    /**
     * @var SimpleCallable
     */
    private $queueCreatorFactoryMock;

    function let(SimpleCallable $messagePusherFactory, SimpleCallable $queueCreatorFactory)
    {
        $this->messagePusherFactoryMock = $messagePusherFactory;
        $this->queueCreatorFactoryMock = $queueCreatorFactory;

        $this->beConstructedWith($messagePusherFactory, $queueCreatorFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\Config\TransportReceiveInfrastructure');
    }

    function it_contains_the_message_pusher_factory_set_at_construction()
    {
        $this->getMessagePusherFactory()->shouldReturn($this->messagePusherFactoryMock);
    }

    function it_contains_the_queue_creator_factory_set_at_construction()
    {
        $this->getQueueCreatorFactory()->shouldReturn($this->queueCreatorFactoryMock);
    }
}
