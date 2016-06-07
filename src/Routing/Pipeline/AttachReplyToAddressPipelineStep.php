<?php
namespace PSB\Core\Routing\Pipeline;


use PSB\Core\HeaderTypeEnum;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\PipelineStepInterface;

class AttachReplyToAddressPipelineStep implements PipelineStepInterface
{
    /**
     * @var string
     */
    private $replyToAddress;

    /**
     * @param string $replyToAddress
     */
    public function __construct($replyToAddress)
    {
        $this->replyToAddress = $replyToAddress;
    }

    /**
     * @param OutgoingLogicalMessageContext $context
     * @param callable                      $next
     */
    public function invoke($context, callable $next)
    {
        $context->setHeader(HeaderTypeEnum::REPLY_TO_ADDRESS, $this->replyToAddress);

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
