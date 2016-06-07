<?php
namespace PSB\Core\MessageMutation\Pipeline\Outgoing;


use PSB\Core\MessageMutatorRegistry;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPhysicalMessageContext;
use PSB\Core\Pipeline\PipelineStepInterface;

class OutgoingPhysicalMessageMutationPipelineStep implements PipelineStepInterface
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
     * @param OutgoingPhysicalMessageContext $context
     * @param callable                       $next
     */
    public function invoke($context, callable $next)
    {
        $mutatorIds = $this->mutatorRegistry->getOutgoingPhysicalMessageMutatorIds();

        if (!$mutatorIds) {
            $next();
            return;
        }

        $physicalMessage = $context->getMessage();
        $mutatorContext = new OutgoingPhysicalMessageMutationContext(
            $physicalMessage->getBody(),
            $physicalMessage->getHeaders()
        );

        foreach ($mutatorIds as $mutatorId) {
            /** @var OutgoingPhysicalMessageMutatorInterface $mutator */
            $mutator = $context->getBuilder()->build($mutatorId);
            $mutator->mutateOutgoing($mutatorContext);
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
        return OutgoingPhysicalMessageContext::class;
    }
}
