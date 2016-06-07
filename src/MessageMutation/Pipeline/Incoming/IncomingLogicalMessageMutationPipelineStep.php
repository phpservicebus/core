<?php
namespace PSB\Core\MessageMutation\Pipeline\Incoming;


use PSB\Core\MessageMutatorRegistry;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactory;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingLogicalMessageContext;
use PSB\Core\Pipeline\PipelineStepInterface;

class IncomingLogicalMessageMutationPipelineStep implements PipelineStepInterface
{
    /**
     * @var MessageMutatorRegistry
     */
    private $mutatorRegistry;

    /**
     * @var IncomingLogicalMessageFactory
     */
    private $messageFactory;

    /**
     * @param MessageMutatorRegistry        $mutatorRegistry
     * @param IncomingLogicalMessageFactory $messageFactory
     */
    public function __construct(MessageMutatorRegistry $mutatorRegistry, IncomingLogicalMessageFactory $messageFactory)
    {
        $this->mutatorRegistry = $mutatorRegistry;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param IncomingLogicalMessageContext $context
     * @param callable                      $next
     */
    public function invoke($context, callable $next)
    {
        $mutatorIds = $this->mutatorRegistry->getIncomingLogicalMessageMutatorIds();

        if (!$mutatorIds) {
            $next();
            return;
        }

        $logicalMessage = $context->getMessage();
        $messageInstance = $logicalMessage->getMessageInstance();

        $mutatorContext = new IncomingLogicalMessageMutationContext($messageInstance, $context->getHeaders());

        foreach ($mutatorIds as $mutatorId) {
            /** @var IncomingLogicalMessageMutatorInterface $mutator */
            $mutator = $context->getBuilder()->build($mutatorId);
            $mutator->mutateIncoming($mutatorContext);
        }

        if ($mutatorContext->hasMessageChanged()) {
            $logicalMessage->updateInstance($mutatorContext->getMessage(), $this->messageFactory);
        }
        $context->replaceHeaders($mutatorContext->getHeaders());

        $next();
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return IncomingLogicalMessageContext::class;
    }
}