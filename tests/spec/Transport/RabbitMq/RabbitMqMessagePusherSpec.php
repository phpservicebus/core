<?php

namespace spec\PSB\Core\Transport\RabbitMq;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Transport\PushPipe;
use PSB\Core\Transport\PushSettings;
use PSB\Core\Transport\RabbitMq\BrokerModel;
use PSB\Core\Transport\RabbitMq\MessageProcessor;
use PSB\Core\Transport\RabbitMq\RabbitMqMessagePusher;

/**
 * @mixin RabbitMqMessagePusher
 */
class RabbitMqMessagePusherSpec extends ObjectBehavior
{
    function it_is_initializable(
        BrokerModel $brokerModel,
        MessageProcessor $messageProcessor
    ) {
        $this->beConstructedWith($brokerModel, $messageProcessor);
        $this->shouldHaveType('PSB\Core\Transport\RabbitMq\RabbitMqMessagePusher');
    }

    function it_initializes_by_purging_the_queue_if_enabled_in_settings(
        BrokerModel $brokerModel,
        MessageProcessor $messageProcessor,
        PushPipe $pushPipe,
        PushSettings $pushSettings
    ) {
        $this->beConstructedWith($brokerModel, $messageProcessor);
        $pushSettings->isPurgeOnStartup()->willReturn(true);
        $pushSettings->getInputQueue()->willReturn('irrelevant name');

        $brokerModel->purgeQueue('irrelevant name')->shouldBeCalled();

        $this->init($pushPipe, $pushSettings);
    }

    function it_initializes_without_purging_if_not_enabled_in_settings(
        BrokerModel $brokerModel,
        MessageProcessor $messageProcessor,
        PushPipe $pushPipe,
        PushSettings $pushSettings
    ) {
        $this->beConstructedWith($brokerModel, $messageProcessor);
        $pushSettings->isPurgeOnStartup()->willReturn(false);

        $brokerModel->purgeQueue(Argument::any())->shouldNotBeCalled();

        $this->init($pushPipe, $pushSettings);
    }

    function it_can_start_pushing_consumed_messages(
        BrokerModel $brokerModel,
        MessageProcessor $messageProcessor,
        PushPipe $pushPipe,
        PushSettings $pushSettings
    ) {
        $this->beConstructedWith($brokerModel, $messageProcessor);
        $pushSettings->getInputQueue()->willReturn('irrelevant name');
        $pushSettings->isPurgeOnStartup()->willReturn(false);

        $brokerModel->consume('irrelevant name', Argument::type('\Closure'))->shouldBeCalled();

        $this->init($pushPipe, $pushSettings);
        $this->start();
    }
}
