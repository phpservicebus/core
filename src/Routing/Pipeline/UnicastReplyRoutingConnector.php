<?php
namespace PSB\Core\Routing\Pipeline;


use PSB\Core\Exception\RoutingException;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\MessageIntentEnum;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingReplyContext;
use PSB\Core\Pipeline\StageConnectorInterface;
use PSB\Core\Transport\IncomingPhysicalMessage;

class UnicastReplyRoutingConnector implements StageConnectorInterface
{
    /**
     * @var OutgoingContextFactory
     */
    private $contextFactory;

    /**
     * @param OutgoingContextFactory $contextFactory
     */
    public function __construct(OutgoingContextFactory $contextFactory)
    {
        $this->contextFactory = $contextFactory;
    }

    /**
     * @param OutgoingReplyContext $context
     * @param callable             $next
     */
    public function invoke($context, callable $next)
    {
        $replyToAddress = $context->getReplyOptions()->getExplicitDestination();
        if (!$replyToAddress) {
            $replyToAddress = $this->getReplyToAddressFromIncomingMessage($context->getIncomingPhysicalMessage());
        }

        $context->setHeader(HeaderTypeEnum::MESSAGE_INTENT, MessageIntentEnum::REPLY);

        $logicalMessageContext = $this->contextFactory->createLogicalMessageContextFromReplyContext(
            $replyToAddress,
            $context
        );

        $next($logicalMessageContext);
    }

    /**
     * @param IncomingPhysicalMessage|null $incomingPhysicalMessage
     *
     * @return string
     */
    private function getReplyToAddressFromIncomingMessage(IncomingPhysicalMessage $incomingPhysicalMessage = null)
    {
        if (!$incomingPhysicalMessage) {
            throw new RoutingException(
                "No incoming message found, replies are only valid to call from a message handler."
            );
        }

        $replyToAddress = $incomingPhysicalMessage->getReplyToAddress();
        if ($replyToAddress === null || $replyToAddress === '') {
            $messageType = $incomingPhysicalMessage->getHeaders()[HeaderTypeEnum::ENCLOSED_CLASS];
            throw new RoutingException("No 'ReplyToAddress' found on the '$messageType' being processed");
        }

        return $incomingPhysicalMessage->getReplyToAddress();
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return OutgoingReplyContext::class;
    }

    /**
     * @return string
     */
    public static function getNextStageContextClass()
    {
        return OutgoingLogicalMessageContext::class;
    }
}
