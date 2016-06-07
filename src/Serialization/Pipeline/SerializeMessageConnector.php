<?php
namespace PSB\Core\Serialization\Pipeline;


use PSB\Core\HeaderTypeEnum;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPhysicalMessageContext;
use PSB\Core\Pipeline\StageConnectorInterface;
use PSB\Core\Serialization\MessageSerializerInterface;

class SerializeMessageConnector implements StageConnectorInterface
{
    /**
     * @var MessageSerializerInterface
     */
    private $messageSerializer;

    /**
     * @var OutgoingContextFactory
     */
    private $contextFactory;

    /**
     * @param MessageSerializerInterface $messageSerializer
     * @param OutgoingContextFactory     $contextFactory
     */
    public function __construct(MessageSerializerInterface $messageSerializer, OutgoingContextFactory $contextFactory)
    {
        $this->messageSerializer = $messageSerializer;
        $this->contextFactory = $contextFactory;
    }

    /**
     * @param OutgoingLogicalMessageContext $context
     * @param callable                      $next
     */
    public function invoke($context, callable $next)
    {
        $context->setHeader(HeaderTypeEnum::CONTENT_TYPE, $this->messageSerializer->getContentType());
        $context->setHeader(HeaderTypeEnum::ENCLOSED_CLASS, $context->getMessage()->getMessageClass());

        $body = $this->messageSerializer->serialize($context->getMessage()->getMessageInstance());
        $outgoingPhysicalContext = $this->contextFactory->createPhysicalMessageContext($body, $context);

        $next($outgoingPhysicalContext);
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return OutgoingLogicalMessageContext::class;
    }

    /**
     * @return string
     */
    public static function getNextStageContextClass()
    {
        return OutgoingPhysicalMessageContext::class;
    }
}
