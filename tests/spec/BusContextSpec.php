<?php

namespace spec\PSB\Core;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\BusContext;
use PSB\Core\OutgoingOptionsFactory;
use PSB\Core\Pipeline\BusOperations;
use PSB\Core\Pipeline\PipelineRootStageContext;
use PSB\Core\PublishOptions;
use PSB\Core\SendOptions;
use PSB\Core\SubscribeOptions;
use PSB\Core\UnsubscribeOptions;

/**
 * @mixin BusContext
 */
class BusContextSpec extends ObjectBehavior
{
    /**
     * @var PipelineRootStageContext
     */
    private $rootContextMock;

    /**
     * @var BusOperations
     */
    private $busOperationsMock;

    /**
     * @var OutgoingOptionsFactory
     */
    private $outgoingOptionsFactoryMock;

    function let(
        PipelineRootStageContext $rootContext,
        BusOperations $busOperations,
        OutgoingOptionsFactory $outgoingOptionsFactory
    ) {
        $this->rootContextMock = $rootContext;
        $this->busOperationsMock = $busOperations;
        $this->outgoingOptionsFactoryMock = $outgoingOptionsFactory;

        $this->beConstructedWith($rootContext, $busOperations, $outgoingOptionsFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\BusContext');
    }

    function it_sends_a_message_with_given_options(SendOptions $options)
    {
        $irrelevantMessage = new \stdClass();

        $this->outgoingOptionsFactoryMock->createSendOptions()->shouldNotBeCalled();
        $this->busOperationsMock->send($irrelevantMessage, $options, $this->rootContextMock)->shouldBeCalled();

        $this->send($irrelevantMessage, $options);
    }

    function it_sends_a_message_with_no_options_provided(
        SendOptions $options
    ) {
        $irrelevantMessage = new \stdClass();

        $this->outgoingOptionsFactoryMock->createSendOptions()->shouldBeCalled()->willReturn($options);
        $this->busOperationsMock->send($irrelevantMessage, $options, $this->rootContextMock)->shouldBeCalled();

        $this->send($irrelevantMessage);
    }

    function it_sends_a_message_to_local_endpoint(SendOptions $options)
    {
        $irrelevantMessage = new \stdClass();

        $options->routeToLocalEndpointInstance()->shouldBeCalled();
        $this->busOperationsMock->send($irrelevantMessage, $options, $this->rootContextMock)->shouldBeCalled();

        $this->sendLocal($irrelevantMessage, $options);
    }

    function it_publishes_a_message_with_given_options(PublishOptions $options)
    {
        $irrelevantMessage = new \stdClass();

        $this->outgoingOptionsFactoryMock->createPublishOptions()->shouldNotBeCalled();
        $this->busOperationsMock->publish($irrelevantMessage, $options, $this->rootContextMock)->shouldBeCalled();

        $this->publish($irrelevantMessage, $options);
    }

    function it_publishes_a_message_with_no_options_provided(
        PublishOptions $options
    ) {
        $irrelevantMessage = new \stdClass();

        $this->outgoingOptionsFactoryMock->createPublishOptions()->shouldBeCalled()->willReturn($options);
        $this->busOperationsMock->publish($irrelevantMessage, $options, $this->rootContextMock)->shouldBeCalled();

        $this->publish($irrelevantMessage);
    }

    function it_subscribes_for_an_event_with_given_options(SubscribeOptions $options)
    {
        $irrelevantEvent = 'event';

        $this->outgoingOptionsFactoryMock->createSubscribeOptions()->shouldNotBeCalled();
        $this->busOperationsMock->subscribe($irrelevantEvent, $options, $this->rootContextMock)->shouldBeCalled();

        $this->subscribe($irrelevantEvent, $options);
    }

    function it_subscribes_for_an_event_with_default_options_if_no_options_provided(SubscribeOptions $options)
    {
        $irrelevantEvent = 'event';

        $this->outgoingOptionsFactoryMock->createSubscribeOptions()->shouldBeCalled()->willReturn($options);
        $this->busOperationsMock->subscribe($irrelevantEvent, $options, $this->rootContextMock)->shouldBeCalled();

        $this->subscribe($irrelevantEvent);
    }

    function it_unsubscribes_from_an_event_with_given_options(UnsubscribeOptions $options)
    {
        $irrelevantEvent = 'event';

        $this->outgoingOptionsFactoryMock->createUnsubscribeOptions()->shouldNotBeCalled();
        $this->busOperationsMock->unsubscribe($irrelevantEvent, $options, $this->rootContextMock)->shouldBeCalled();

        $this->unsubscribe($irrelevantEvent, $options);
    }

    function it_unsubscribes_from_an_event_with_default_options_if_no_options_provided(UnsubscribeOptions $options)
    {
        $irrelevantEvent = 'event';

        $this->outgoingOptionsFactoryMock->createUnsubscribeOptions()->shouldBeCalled()->willReturn($options);
        $this->busOperationsMock->unsubscribe($irrelevantEvent, $options, $this->rootContextMock)->shouldBeCalled();

        $this->unsubscribe($irrelevantEvent);
    }
}
