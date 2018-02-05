<?php

namespace spec\PSB\Core\Transport\Config;

use PhpSpec\ObjectBehavior;

use PSB\Core\Transport\Config\TransportSendInfrastructure;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin TransportSendInfrastructure
 */
class TransportSendInfrastructureSpec extends ObjectBehavior
{
    /**
     * @var \specsupport\PSB\Core\SimpleCallable
     */
    private $messageDispatcherFactoryMock;

    function let(SimpleCallable $messageDispatcherFactory)
    {
        $this->messageDispatcherFactoryMock = $messageDispatcherFactory;

        $this->beConstructedWith($messageDispatcherFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\Config\TransportSendInfrastructure');
    }

    function it_contains_the_message_dispatcher_factory_set_at_construction()
    {
        $this->getMessageDispatcherFactory()->shouldReturn($this->messageDispatcherFactoryMock);
    }
}
