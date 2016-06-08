<?php
namespace PSB\Core\MessageMutation\Pipeline\Incoming;


use PSB\Core\MessageMutatorRegistry;
use PSB\Core\Pipeline\Incoming\StageContext\IncomingPhysicalMessageContext;
use PSB\Core\Pipeline\PipelineStepInterface;

class IncomingPhysicalMessageMutationPipelineStep implements PipelineStepInterface
{
    /**
     * @var MessageMutatorRegistry
     */
    private $mutatorRegistry;

    /**
     * IncomingPhysicalMessageMutationPipelineStep constructor.
     *
     * @param MessageMutatorRegistry $mutatorRegistry
     */
    public function __construct(MessageMutatorRegistry $mutatorRegistry)
    {
        $this->mutatorRegistry = $mutatorRegistry;
    }

    /**
     * @param IncomingPhysicalMessageContext $context
     * @param callable                       $next
     */
    public function invoke($context, callable $next)
    {
        $mutatorIds = $this->mutatorRegistry->getIncomingPhysicalMessageMutatorIds();

        if (empty($mutatorIds)) {
            $next();
            return;
        }

        $physicalMessage = $context->getMessage();
        $mutatorContext = new IncomingPhysicalMessageMutationContext(
            $physicalMessage->getBody(),
            $physicalMessage->getHeaders()
        );

        foreach ($mutatorIds as $mutatorId) {
            /** @var IncomingPhysicalMessageMutatorInterface $mutator */
            $mutator = $context->getBuilder()->build($mutatorId);
            $mutator->mutateIncoming($mutatorContext);
        }

        $physicalMessage->replaceBody($mutatorContext->getBody());
        $physicalMessage->replaceHeaders($mutatorContext->getHeaders());

        $next();
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return IncomingPhysicalMessageContext::class;
    }
}
