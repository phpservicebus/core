<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;

use PSB\Core\Transport\MessagePusherInterface;
use PSB\Core\Transport\PushPipe;
use PSB\Core\Transport\PushSettings;
use PSB\Core\Transport\TransportReceiver;

/**
 * @mixin TransportReceiver
 */
class TransportReceiverSpec extends ObjectBehavior
{
    function it_initializes_and_starts_the_mesage_pusher(
        MessagePusherInterface $messagePusher,
        PushSettings $pushSettings,
        PushPipe $pushPipe
    ) {
        $this->beConstructedWith($messagePusher, $pushSettings, $pushPipe);
        $this->shouldHaveType('PSB\Core\Transport\TransportReceiver');

        $messagePusher->init($pushPipe, $pushSettings)->shouldBeCalled();
        $messagePusher->start()->shouldBeCalled();

        $this->start();
    }
}
