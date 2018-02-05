<?php

namespace spec\PSB\Core;

use PhpSpec\ObjectBehavior;

use PSB\Core\BusContextInterface;
use PSB\Core\EndpointInstance;
use PSB\Core\PublishOptions;
use PSB\Core\SendOptions;
use PSB\Core\SubscribeOptions;
use PSB\Core\UnsubscribeOptions;

/**
 * @mixin EndpointInstance
 */
class EndpointInstanceSpec extends ObjectBehavior
{
    /**
     * @var BusContextInterface
     */
    private $busContextMock;

    public function let(BusContextInterface $busContext)
    {
        $this->busContextMock = $busContext;
        $this->beConstructedWith($busContext);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\EndpointInstance');
    }

    function it_sends_a_message(SendOptions $options)
    {
        $message = new \stdClass();
        $this->busContextMock->send($message, $options)->shouldBeCalled();
        $this->send($message, $options);
    }

    function it_sends_a_message_locally(SendOptions $options)
    {
        $message = new \stdClass();
        $this->busContextMock->sendLocal($message, $options)->shouldBeCalled();
        $this->sendLocal($message, $options);
    }

    function it_publishes_a_message(PublishOptions $options)
    {
        $message = new \stdClass();
        $this->busContextMock->publish($message, $options)->shouldBeCalled();
        $this->publish($message, $options);
    }

    function it_subscribes_to_a_message(SubscribeOptions $options)
    {
        $eventFqcn = 'irrelevant';
        $this->busContextMock->subscribe($eventFqcn, $options)->shouldBeCalled();
        $this->subscribe($eventFqcn, $options);
    }

    function it_unsubscribes_to_a_message(UnsubscribeOptions $options)
    {
        $eventFqcn = 'irrelevant';
        $this->busContextMock->unsubscribe($eventFqcn, $options)->shouldBeCalled();
        $this->unsubscribe($eventFqcn, $options);
    }
}
