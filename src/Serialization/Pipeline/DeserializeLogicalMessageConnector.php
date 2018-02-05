<?php
namespace PSB\Core\Serialization\Pipeline;


use PSB\Core\Exception\MessageDeserializationException;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\Pipeline\Incoming\IncomingContextFactory;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessage;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactory;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingLogicalMessageContext;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingPhysicalMessageContext;
use PSB\Core\Pipeline\StageConnectorInterface;
use PSB\Core\Serialization\MessageDeserializerResolver;
use PSB\Core\Transport\IncomingPhysicalMessage;

class DeserializeLogicalMessageConnector implements StageConnectorInterface
{
    /**
     * @var MessageDeserializerResolver
     */
    private $deserializerResolver;

    /**
     * @var IncomingLogicalMessageFactory
     */
    private $logicalMessageFactory;

    /**
     * @var IncomingContextFactory
     */
    private $incomingContextFactory;

    /**
     * @param MessageDeserializerResolver   $deserializerResolver
     * @param IncomingLogicalMessageFactory $logicalMessageFactory
     * @param IncomingContextFactory        $incomingContextFactory
     */
    public function __construct(
        MessageDeserializerResolver $deserializerResolver,
        IncomingLogicalMessageFactory $logicalMessageFactory,
        IncomingContextFactory $incomingContextFactory
    ) {

        $this->deserializerResolver = $deserializerResolver;
        $this->logicalMessageFactory = $logicalMessageFactory;
        $this->incomingContextFactory = $incomingContextFactory;
    }

    /**
     * @param IncomingPhysicalMessageContext $context
     * @param callable                       $next
     */
    public function invoke($context, callable $next)
    {
        $physicalMessage = $context->getMessage();

        $logicalMessage = $this->extractWithExceptionHandling($physicalMessage);

        if (!$logicalMessage) {
            throw new MessageDeserializationException($physicalMessage->getMessageId());
        }

        $next($this->incomingContextFactory->createLogicalMessageContext($logicalMessage, $context));
    }

    /**
     * @param IncomingPhysicalMessage $physicalMessage
     *
     * @return IncomingLogicalMessage|null
     */
    private function extractWithExceptionHandling(IncomingPhysicalMessage $physicalMessage)
    {
        try {
            return $this->extractMessage($physicalMessage);
        } catch (\Throwable $t) {
            throw new MessageDeserializationException($physicalMessage->getMessageId(), $t);
        }
    }

    /**
     * @param IncomingPhysicalMessage $physicalMessage
     *
     * @return IncomingLogicalMessage|null
     */
    private function extractMessage(IncomingPhysicalMessage $physicalMessage)
    {
        $body = $physicalMessage->getBody();
        if ($body === null || $body === '') {
            return null;
        }

        if (!isset($physicalMessage->getHeaders()[HeaderTypeEnum::ENCLOSED_CLASS])) {
            return null;
        }

        $messageClass = $physicalMessage->getHeaders()[HeaderTypeEnum::ENCLOSED_CLASS];
        $serializer = $this->deserializerResolver->resolve($physicalMessage->getHeaders());
        $instanceMessage = $serializer->deserialize($body, $messageClass);

        return $this->logicalMessageFactory->createFromObject($instanceMessage);
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return IncomingPhysicalMessageContext::class;
    }

    /**
     * @return string
     */
    public static function getNextStageContextClass()
    {
        return IncomingLogicalMessageContext::class;
    }
}
