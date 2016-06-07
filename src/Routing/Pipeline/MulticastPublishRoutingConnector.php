<?php
namespace PSB\Core\Routing\Pipeline;


use PSB\Core\HeaderTypeEnum;
use PSB\Core\MessageIntentEnum;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPublishContext;
use PSB\Core\Pipeline\StageConnectorInterface;

class MulticastPublishRoutingConnector implements StageConnectorInterface
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
     * @param OutgoingPublishContext $context
     * @param callable               $next
     */
    public function invoke($context, callable $next)
    {
        $context->setHeader(HeaderTypeEnum::MESSAGE_INTENT, MessageIntentEnum::PUBLISH);

        $logicalMesageContext = $this->contextFactory->createLogicalMessageContextFromPublishContext($context);

        $next($logicalMesageContext);
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return OutgoingPublishContext::class;
    }

    /**
     * @return string
     */
    public static function getNextStageContextClass()
    {
        return OutgoingLogicalMessageContext::class;
    }
}
