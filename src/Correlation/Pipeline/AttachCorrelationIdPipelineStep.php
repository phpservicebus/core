<?php
namespace PSB\Core\Correlation\Pipeline;


use PSB\Core\HeaderTypeEnum;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\PipelineStepInterface;

class AttachCorrelationIdPipelineStep implements PipelineStepInterface
{
    /**
     * @param OutgoingLogicalMessageContext $context
     * @param callable                      $next
     */
    public function invoke($context, callable $next)
    {
        $correlationId = null;

        if ($context->getIncomingPhysicalMessage()) {
            $incomingHeaders = $context->getIncomingPhysicalMessage()->getHeaders();

            if (isset($incomingHeaders[HeaderTypeEnum::CORRELATION_ID])) {
                $correlationId = $incomingHeaders[HeaderTypeEnum::CORRELATION_ID];
            }

            if (!$correlationId && isset($incomingHeaders[HeaderTypeEnum::MESSAGE_ID])) {
                $correlationId = $incomingHeaders[HeaderTypeEnum::MESSAGE_ID];
            }
        }

        if (!$correlationId) {
            $correlationId = $context->getMessageId();
        }

        $context->setHeader(HeaderTypeEnum::CORRELATION_ID, $correlationId);

        $next();
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return OutgoingLogicalMessageContext::class;
    }
}
