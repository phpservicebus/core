<?php
namespace PSB\Core\Pipeline\Incoming;


use PSB\Core\Exception\UnexpectedValueException;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\MessageHandlerRegistry;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingLogicalMessageContext;
use PSB\Core\Pipeline\Incoming\StageContext\InvokeHandlerContext;
use PSB\Core\Pipeline\StageConnectorInterface;

class LoadHandlersConnector implements StageConnectorInterface
{

    /**
     * @var MessageHandlerRegistry
     */
    private $messageHandlerRegistry;

    /**
     * @var IncomingContextFactory
     */
    private $incomingContextFactory;

    /**
     * @param MessageHandlerRegistry $messageHandlerRegistry
     * @param IncomingContextFactory $incomingContextFactory
     */
    public function __construct(
        MessageHandlerRegistry $messageHandlerRegistry,
        IncomingContextFactory $incomingContextFactory
    ) {
        $this->messageHandlerRegistry = $messageHandlerRegistry;
        $this->incomingContextFactory = $incomingContextFactory;
    }

    /**
     * @param IncomingLogicalMessageContext $context
     * @param callable                      $next
     */
    public function invoke($context, callable $next)
    {
        $messageTypes = $context->getMessage()->getMessageTypes();
        $handlerIdsToInvoke = $this->messageHandlerRegistry->getHandlerIdsFor($messageTypes);

        if (!$context->isMessageHandled() && !count($handlerIdsToInvoke)) {
            $messageTypes = implode(',', $messageTypes);
            throw new UnexpectedValueException("No message handlers could be found for message types '$messageTypes'.");
        }

        foreach ($handlerIdsToInvoke as $handlerId) {
            /** @var MessageHandlerInterface $handler */
            $handler = $context->getBuilder()->build($handlerId);
            $handlingContext = $this->incomingContextFactory->createInvokeHandlerContext($handler, $context);
            $next($handlingContext);

            if ($handlingContext->isHandlerInvocationAborted()) {
                //if the chain was aborted skip the other handlers
                break;
            }
        }

        $context->markMessageAsHandled();
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return IncomingLogicalMessageContext::class;
    }

    /**
     * @return string
     */
    public static function getNextStageContextClass()
    {
        return InvokeHandlerContext::class;
    }
}
