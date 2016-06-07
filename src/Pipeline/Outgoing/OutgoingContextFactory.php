<?php
namespace PSB\Core\Pipeline\Outgoing;


use PSB\Core\Pipeline\Outgoing\StageContext\DispatchContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPhysicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPublishContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingReplyContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingSendContext;
use PSB\Core\Pipeline\PipelineStageContext;
use PSB\Core\Routing\AddressTagInterface;
use PSB\Core\Routing\MulticastAddressTag;
use PSB\Core\Routing\UnicastAddressTag;
use PSB\Core\Transport\OutgoingPhysicalMessage;

class OutgoingContextFactory
{
    /**
     * @param OutgoingPublishContext $parentContext
     *
     * @return OutgoingLogicalMessageContext
     */
    public function createLogicalMessageContextFromPublishContext(
        OutgoingPublishContext $parentContext
    ) {
        return new OutgoingLogicalMessageContext(
            $parentContext->getMessageId(),
            $parentContext->getHeaders(),
            $parentContext->getLogicalMessage(),
            [new MulticastAddressTag($parentContext->getLogicalMessage()->getMessageClass())],
            $parentContext->getPublishOptions()->isImmediateDispatchEnabled(),
            $parentContext->getIncomingPhysicalMessage(),
            $parentContext->getPendingTransportOperations(),
            $parentContext
        );
    }

    /**
     * @param AddressTagInterface[] $addressTags
     * @param OutgoingSendContext   $parentContext
     *
     * @return OutgoingLogicalMessageContext
     */
    public function createLogicalMessageContextFromSendContext(
        array $addressTags,
        OutgoingSendContext $parentContext
    ) {
        return new OutgoingLogicalMessageContext(
            $parentContext->getMessageId(),
            $parentContext->getHeaders(),
            $parentContext->getLogicalMessage(),
            $addressTags,
            $parentContext->getSendOptions()->isImmediateDispatchEnabled(),
            $parentContext->getIncomingPhysicalMessage(),
            $parentContext->getPendingTransportOperations(),
            $parentContext
        );
    }

    /**
     * @param string               $replyToAddress
     * @param OutgoingReplyContext $parentContext
     *
     * @return OutgoingLogicalMessageContext
     */
    public function createLogicalMessageContextFromReplyContext($replyToAddress, OutgoingReplyContext $parentContext)
    {
        return new OutgoingLogicalMessageContext(
            $parentContext->getMessageId(),
            $parentContext->getHeaders(),
            $parentContext->getLogicalMessage(),
            [new UnicastAddressTag($replyToAddress)],
            $parentContext->getReplyOptions()->isImmediateDispatchEnabled(),
            $parentContext->getIncomingPhysicalMessage(),
            $parentContext->getPendingTransportOperations(),
            $parentContext
        );
    }

    /**
     * @param string                        $body
     * @param OutgoingLogicalMessageContext $parentContext
     *
     * @return OutgoingPhysicalMessageContext
     */
    public function createPhysicalMessageContext($body, OutgoingLogicalMessageContext $parentContext)
    {
        return new OutgoingPhysicalMessageContext(
            $parentContext->getMessageId(),
            $parentContext->getHeaders(),
            new OutgoingPhysicalMessage($parentContext->getMessageId(), $parentContext->getHeaders(), $body),
            $parentContext->getAddressTags(),
            $parentContext->isImmediateDispatchEnabled(),
            $parentContext->getIncomingPhysicalMessage(),
            $parentContext->getPendingTransportOperations(),
            $parentContext
        );
    }

    /**
     * @param array                $transportOperations
     * @param PipelineStageContext $parentContext
     *
     * @return DispatchContext
     */
    public function createDispatchContext(
        array $transportOperations,
        PipelineStageContext $parentContext
    ) {
        return new DispatchContext($transportOperations, $parentContext);
    }
}
