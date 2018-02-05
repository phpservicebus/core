<?php

namespace spec\PSB\Core\Outbox;

use PhpSpec\ObjectBehavior;

use PSB\Core\Outbox\OutboxTransportOperation;
use PSB\Core\Outbox\OutboxTransportOperationFactory;
use PSB\Core\Transport\OutgoingPhysicalMessage;

/**
 * @mixin OutboxTransportOperationFactory
 */
class OutboxTransportOperationFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Outbox\OutboxTransportOperationFactory');
    }

    function it_creates_one(OutgoingPhysicalMessage $physicalMessage)
    {
        $options = ['whatever'];
        $physicalMessage->getMessageId()->willReturn('someid');
        $physicalMessage->getBody()->willReturn('somebody');
        $physicalMessage->getHeaders()->willReturn(['some' => 'header']);

        $this->create($physicalMessage, $options)->shouldBeLike(
            new OutboxTransportOperation(
                'someid',
                $options,
                'somebody',
                ['some' => 'header']
            )
        );
    }
}
