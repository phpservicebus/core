<?php
namespace PSB\Core\MessageMutation\Pipeline\Outgoing;


use PSB\Core\MessageMutatorRegistry;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingLogicalMessageContext;
use PSB\Core\Pipeline\PipelineStepInterface;

class OutgoingLogicalMessageMutationPipelineStep implements PipelineStepInterface
{
    /**
     * @var MessageMutatorRegistry
     */
    private $mutatorRegistry;

    /**
     * @param MessageMutatorRegistry $mutatorRegistry
     */
    public function __construct(MessageMutatorRegistry $mutatorRegistry)
    {
        $this->mutatorRegistry = $mutatorRegistry;
    }

    /**
     * @param OutgoingLogicalMessageContext $context
     * @param callable                      $next
     */
    public function invoke($context, callable $next)
    {
        $mutatorIds = $this->mutatorRegistry->getOutgoingLogicalMessageMutatorIds();

        if (!$mutatorIds) {
            $next();
            return;
        }

        $logicalMessage = $context->getMessage();
        $messageInstance = $logicalMessage->getMessageInstance();

        $mutatorContext = new OutgoingLogicalMessageMutationContext($messageInstance, $context->getHeaders());

        foreach ($mutatorIds as $mutatorId) {
            /** @var OutgoingLogicalMessageMutatorInterface $mutator */
            $mutator = $context->getBuilder()->build($mutatorId);
            $mutator->mutateOutgoing($mutatorContext);
        }

        if ($mutatorContext->hasMessageChanged()) {
            $logicalMessage->updateInstance($mutatorContext->getMessage());
        }
        $context->replaceHeaders($mutatorContext->getHeaders());

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
