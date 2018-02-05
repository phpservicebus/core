<?php

namespace spec\PSB\Core\Pipeline\Outgoing;

use PhpSpec\ObjectBehavior;

use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessage;
use PSB\Core\Pipeline\Outgoing\StageContext\DispatchContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPhysicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPublishContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingReplyContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingSendContext;
use PSB\Core\Pipeline\PendingTransportOperations;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\PublishOptions;
use PSB\Core\ReplyOptions;
use PSB\Core\Routing\MulticastAddressTag;
use PSB\Core\Routing\UnicastAddressTag;
use PSB\Core\SendOptions;
use PSB\Core\Transport\IncomingPhysicalMessage;
use PSB\Core\Transport\OutgoingPhysicalMessage;
use PSB\Core\Transport\TransportOperation;

/**
 * @mixin OutgoingContextFactory
 */
class OutgoingContextFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Pipeline\Outgoing\OutgoingContextFactory');
    }

    function it_creates_a_logical_message_context_from_a_publish_context(
        OutgoingPublishContext $parentContext,
        OutgoingLogicalMessage $outgoingLogicalMessage,
        PublishOptions $publishOptions,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $pendingTransportOperations
    ) {
        $parentContext->getMessageId()->willReturn('irrelevant');
        $parentContext->getHeaders()->willReturn(['irrele' => 'vant']);
        $parentContext->getLogicalMessage()->willReturn($outgoingLogicalMessage);
        $outgoingLogicalMessage->getMessageClass()->willReturn('someclass');
        $parentContext->getPublishOptions()->willReturn($publishOptions);
        $publishOptions->isImmediateDispatchEnabled()->willReturn(true);
        $parentContext->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $parentContext->getPendingTransportOperations()->willReturn($pendingTransportOperations);
        $this->createLogicalMessageContextFromPublishContext($parentContext)->shouldBeLike(
            new OutgoingLogicalMessageContext(
                'irrelevant',
                ['irrele' => 'vant'],
                $outgoingLogicalMessage->getWrappedObject(),
                [new MulticastAddressTag('someclass')],
                true,
                $incomingPhysicalMessage->getWrappedObject(),
                $pendingTransportOperations->getWrappedObject(),
                $parentContext->getWrappedObject()
            )
        );
    }

    function it_creates_a_logical_message_context_from_a_send_context(
        OutgoingSendContext $parentContext,
        OutgoingLogicalMessage $outgoingLogicalMessage,
        SendOptions $sendOptions,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $pendingTransportOperations
    ) {
        $addressTags = [new MulticastAddressTag('someclass')];
        $parentContext->getMessageId()->willReturn('irrelevant');
        $parentContext->getHeaders()->willReturn(['irrele' => 'vant']);
        $parentContext->getLogicalMessage()->willReturn($outgoingLogicalMessage);
        $outgoingLogicalMessage->getMessageClass()->willReturn('someclass');
        $parentContext->getSendOptions()->willReturn($sendOptions);
        $sendOptions->isImmediateDispatchEnabled()->willReturn(true);
        $parentContext->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $parentContext->getPendingTransportOperations()->willReturn($pendingTransportOperations);
        $this->createLogicalMessageContextFromSendContext($addressTags, $parentContext)->shouldBeLike(
            new OutgoingLogicalMessageContext(
                'irrelevant',
                ['irrele' => 'vant'],
                $outgoingLogicalMessage->getWrappedObject(),
                $addressTags,
                true,
                $incomingPhysicalMessage->getWrappedObject(),
                $pendingTransportOperations->getWrappedObject(),
                $parentContext->getWrappedObject()
            )
        );
    }

    function it_creates_a_logical_message_context_from_a_reply_context(
        OutgoingReplyContext $parentContext,
        OutgoingLogicalMessage $outgoingLogicalMessage,
        ReplyOptions $replyOptions,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $pendingTransportOperations
    ) {
        $replyToAddress = 'someclass';
        $parentContext->getMessageId()->willReturn('irrelevant');
        $parentContext->getHeaders()->willReturn(['irrele' => 'vant']);
        $parentContext->getLogicalMessage()->willReturn($outgoingLogicalMessage);
        $outgoingLogicalMessage->getMessageClass()->willReturn('someclass');
        $parentContext->getReplyOptions()->willReturn($replyOptions);
        $replyOptions->isImmediateDispatchEnabled()->willReturn(true);
        $parentContext->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $parentContext->getPendingTransportOperations()->willReturn($pendingTransportOperations);
        $this->createLogicalMessageContextFromReplyContext($replyToAddress, $parentContext)->shouldBeLike(
            new OutgoingLogicalMessageContext(
                'irrelevant',
                ['irrele' => 'vant'],
                $outgoingLogicalMessage->getWrappedObject(),
                [new UnicastAddressTag('someclass')],
                true,
                $incomingPhysicalMessage->getWrappedObject(),
                $pendingTransportOperations->getWrappedObject(),
                $parentContext->getWrappedObject()
            )
        );
    }

    function it_creates_a_physical_message_context(
        OutgoingLogicalMessageContext $parentContext,
        OutgoingLogicalMessage $outgoingLogicalMessage,
        ReplyOptions $replyOptions,
        IncomingPhysicalMessage $incomingPhysicalMessage,
        PendingTransportOperations $pendingTransportOperations
    ) {
        $body = 'somebody';
        $addressTags = [new UnicastAddressTag('someclass')];
        $parentContext->getMessageId()->willReturn('irrelevant');
        $parentContext->getHeaders()->willReturn(['irrele' => 'vant']);
        $parentContext->getAddressTags()->willReturn($addressTags);
        $parentContext->isImmediateDispatchEnabled()->willReturn(true);
        $parentContext->getIncomingPhysicalMessage()->willReturn($incomingPhysicalMessage);
        $parentContext->getPendingTransportOperations()->willReturn($pendingTransportOperations);
        $this->createPhysicalMessageContext($body, $parentContext)->shouldBeLike(
            new OutgoingPhysicalMessageContext(
                'irrelevant',
                ['irrele' => 'vant'],
                new OutgoingPhysicalMessage('irrelevant', ['irrele' => 'vant'], $body),
                [new UnicastAddressTag('someclass')],
                true,
                $incomingPhysicalMessage->getWrappedObject(),
                $pendingTransportOperations->getWrappedObject(),
                $parentContext->getWrappedObject()
            )
        );
    }

    function it_creates_a_dispatch_context(
        PipelineStageContext $parentContext,
        TransportOperation $transportOperation
    ) {
        $transportOperations = [$transportOperation->getWrappedObject()];
        $this->createDispatchContext($transportOperations, $parentContext)->shouldBeLike(
            new DispatchContext($transportOperations, $parentContext->getWrappedObject())
        );
    }
}
