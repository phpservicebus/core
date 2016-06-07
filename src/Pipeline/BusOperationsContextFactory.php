<?php
namespace PSB\Core\Pipeline;


use PSB\Core\Pipeline\Incoming\IncomingContext;
use PSB\Core\Pipeline\Outgoing\OutgoingLogicalMessage;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPublishContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingReplyContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingSendContext;
use PSB\Core\Pipeline\Outgoing\StageContext\SubscribeContext;
use PSB\Core\Pipeline\Outgoing\StageContext\UnsubscribeContext;
use PSB\Core\PublishOptions;
use PSB\Core\ReplyOptions;
use PSB\Core\SendOptions;
use PSB\Core\SubscribeOptions;
use PSB\Core\UnsubscribeOptions;

class BusOperationsContextFactory
{
    /**
     * @param object               $message
     * @param PublishOptions       $options
     * @param PipelineStageContext $parentContext
     *
     * @return OutgoingPublishContext
     */
    public function createPublishContext(
        $message,
        PublishOptions $options,
        PipelineStageContext $parentContext
    ) {
        return new OutgoingPublishContext(
            new OutgoingLogicalMessage($message),
            $options,
            $parentContext instanceof IncomingContext ? $parentContext->getIncomingPhysicalMessage() : null,
            $parentContext instanceof IncomingContext ? $parentContext->getPendingTransportOperations() : null,
            $parentContext
        );
    }

    /**
     * @param object               $message
     * @param SendOptions          $options
     * @param PipelineStageContext $parentContext
     *
     * @return OutgoingSendContext
     */
    public function createSendContext(
        $message,
        SendOptions $options,
        PipelineStageContext $parentContext
    ) {
        return new OutgoingSendContext(
            new OutgoingLogicalMessage($message),
            $options,
            $parentContext instanceof IncomingContext ? $parentContext->getIncomingPhysicalMessage() : null,
            $parentContext instanceof IncomingContext ? $parentContext->getPendingTransportOperations() : null,
            $parentContext
        );
    }

    /**
     * @param object          $message
     * @param ReplyOptions    $options
     * @param IncomingContext $parentContext
     *
     * @return OutgoingReplyContext
     */
    public function createReplyContext(
        $message,
        ReplyOptions $options,
        IncomingContext $parentContext
    ) {
        return new OutgoingReplyContext(
            new OutgoingLogicalMessage($message),
            $options,
            $parentContext->getIncomingPhysicalMessage(),
            $parentContext->getPendingTransportOperations(),
            $parentContext
        );
    }

    /**
     * @param string               $eventFqcn
     * @param SubscribeOptions     $options
     * @param PipelineStageContext $parentContext
     *
     * @return SubscribeContext
     */
    public function createSubscribeContext($eventFqcn, SubscribeOptions $options, PipelineStageContext $parentContext)
    {
        return new SubscribeContext($eventFqcn, $options, $parentContext);
    }

    /**
     * @param string               $eventFqcn
     * @param UnsubscribeOptions   $options
     * @param PipelineStageContext $parentContext
     *
     * @return UnsubscribeContext
     */
    public function createUnsubscribeContext(
        $eventFqcn,
        UnsubscribeOptions $options,
        PipelineStageContext $parentContext
    ) {
        return new UnsubscribeContext($eventFqcn, $options, $parentContext);
    }
}
