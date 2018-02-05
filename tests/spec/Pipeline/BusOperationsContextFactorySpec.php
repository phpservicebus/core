<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;

use PSB\Core\Pipeline\BusOperationsContextFactory;
use PSB\Core\Pipeline\Incoming\IncomingContext;
use PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessage;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPublishContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingReplyContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingSendContext;
use PSB\Core\Pipeline\Outgoing\StageContext\SubscribeContext;
use PSB\Core\Pipeline\Outgoing\StageContext\UnsubscribeContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\PublishOptions;
use PSB\Core\ReplyOptions;
use PSB\Core\SendOptions;
use PSB\Core\SubscribeOptions;
use PSB\Core\Transport\IncomingPhysicalMessage;
use PSB\Core\UnsubscribeOptions;

/**
 * @mixin BusOperationsContextFactory
 */
class BusOperationsContextFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\BusOperationsContextFactory');
    }

    function it_creates_a_publish_context_from_a_non_incoming_context(
        PublishOptions $options,
        PipelineStageContext $parentContext
    ) {
        $message = new \stdClass();
        $options->getOutgoingHeaders()->willReturn([]);
        $options->getMessageId()->willReturn('');
        $this->createPublishContext($message, $options, $parentContext)->shouldBeLike(
            new OutgoingPublishContext(
                new OutgoingLogicalMessage($message),
                $options->getWrappedObject(),
                null,
                null,
                $parentContext->getWrappedObject()
            )
        );
    }

    function it_creates_a_publish_context_from_an_incoming_context(
        PublishOptions $options,
        IncomingContext $parentContext,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $transportOperations
    ) {
        $message = new \stdClass();
        $options->getOutgoingHeaders()->willReturn([]);
        $options->getMessageId()->willReturn('');
        $parentContext->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $parentContext->getPendingTransportOperations()->willReturn($transportOperations);
        $this->createPublishContext($message, $options, $parentContext)->shouldBeLike(
            new OutgoingPublishContext(
                new OutgoingLogicalMessage($message),
                $options->getWrappedObject(),
                $incomingPhysicalMessage->getWrappedObject(),
                $transportOperations->getWrappedObject(),
                $parentContext->getWrappedObject()
            )
        );
    }

    function it_creates_a_send_context_from_a_non_incoming_context(
        SendOptions $options,
        PipelineStageContext $parentContext
    ) {
        $message = new \stdClass();
        $options->getOutgoingHeaders()->willReturn([]);
        $options->getMessageId()->willReturn('');
        $this->createSendContext($message, $options, $parentContext)->shouldBeLike(
            new OutgoingSendContext(
                new OutgoingLogicalMessage($message),
                $options->getWrappedObject(),
                null,
                null,
                $parentContext->getWrappedObject()
            )
        );
    }

    function it_creates_a_send_context_from_an_incoming_context(
        SendOptions $options,
        IncomingContext $parentContext,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $transportOperations
    ) {
        $message = new \stdClass();
        $options->getOutgoingHeaders()->willReturn([]);
        $options->getMessageId()->willReturn('');
        $parentContext->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $parentContext->getPendingTransportOperations()->willReturn($transportOperations);
        $this->createSendContext($message, $options, $parentContext)->shouldBeLike(
            new OutgoingSendContext(
                new OutgoingLogicalMessage($message),
                $options->getWrappedObject(),
                $incomingPhysicalMessage->getWrappedObject(),
                $transportOperations->getWrappedObject(),
                $parentContext->getWrappedObject()
            )
        );
    }

    function it_creates_a_reply_context_from_an_incoming_context(
        ReplyOptions $options,
        IncomingContext $parentContext,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $transportOperations
    ) {
        $message = new \stdClass();
        $options->getOutgoingHeaders()->willReturn([]);
        $options->getMessageId()->willReturn('');
        $parentContext->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $parentContext->getPendingTransportOperations()->willReturn($transportOperations);
        $this->createReplyContext($message, $options, $parentContext)->shouldBeLike(
            new OutgoingReplyContext(
                new OutgoingLogicalMessage($message),
                $options->getWrappedObject(),
                $incomingPhysicalMessage->getWrappedObject(),
                $transportOperations->getWrappedObject(),
                $parentContext->getWrappedObject()
            )
        );
    }

    function it_creates_a_subscribe_context(
        SubscribeOptions $options,
        IncomingContext $parentContext
    ) {
        $eventFqcn = 'irrelevant';
        $this->createSubscribeContext($eventFqcn, $options, $parentContext)->shouldBeLike(
            new SubscribeContext(
                $eventFqcn,
                $options->getWrappedObject(),
                $parentContext->getWrappedObject()
            )
        );
    }

    function it_creates_an_unsubscribe_context(
        UnsubscribeOptions $options,
        IncomingContext $parentContext
    ) {
        $eventFqcn = 'irrelevant';
        $this->createUnsubscribeContext($eventFqcn, $options, $parentContext)->shouldBeLike(
            new UnsubscribeContext(
                $eventFqcn,
                $options->getWrappedObject(),
                $parentContext->getWrappedObject()
            )
        );
    }
}
