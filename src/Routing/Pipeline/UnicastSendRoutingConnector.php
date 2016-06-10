<?php
namespace PSB\Core\Routing\Pipeline;


use PSB\Core\Exception\RoutingException;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\MessageIntentEnum;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingSendContext;
use PSB\Core\Pipeline\StageConnectorInterface;
use PSB\Core\Routing\UnicastRouterInterface;

class UnicastSendRoutingConnector implements StageConnectorInterface
{
    /**
     * @var UnicastRouterInterface
     */
    private $unicastRouter;

    /**
     * @var OutgoingContextFactory
     */
    private $contextFactory;

    /**
     * @param UnicastRouterInterface $unicastRouter
     * @param OutgoingContextFactory $contextFactory
     */
    public function __construct(
        UnicastRouterInterface $unicastRouter,
        OutgoingContextFactory $contextFactory
    ) {
        $this->unicastRouter = $unicastRouter;
        $this->contextFactory = $contextFactory;
    }

    /**
     * @param OutgoingSendContext $context
     * @param callable            $next
     */
    public function invoke($context, callable $next)
    {
        $addressTags = $this->unicastRouter->route($context->getSendOptions(), $context->getMessageClass());

        if (empty($addressTags)) {
            throw new RoutingException(
                "The message destination could not be determined. You may have misconfigured the destination for this kind of message ({$context->getMessageClass()}) when you registered the message to endpoint mappings."
            );
        }

        $context->setHeader(HeaderTypeEnum::MESSAGE_INTENT, MessageIntentEnum::SEND);

        $logicalMessageContext = $this->contextFactory->createLogicalMessageContextFromSendContext(
            $addressTags,
            $context
        );

        $next($logicalMessageContext);
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return OutgoingSendContext::class;
    }

    /**
     * @return string
     */
    public static function getNextStageContextClass()
    {
        return OutgoingLogicalMessageContext::class;
    }
}
