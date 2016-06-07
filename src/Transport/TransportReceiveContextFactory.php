<?php
namespace PSB\Core\Transport;


use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Pipeline\PipelineRootStageContext;

class TransportReceiveContextFactory
{
    /**
     * @var BuilderInterface
     */
    private $builder;

    /**
     * @param BuilderInterface $builder
     */
    public function __construct(BuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    public function createFromPushContext(PushContext $pushContext)
    {
        return new TransportReceiveContext(
            $pushContext->getMessageId(),
            new IncomingPhysicalMessage(
                $pushContext->getMessageId(),
                $pushContext->getHeaders(),
                $pushContext->getBody()
            ),
            $pushContext->getCancellationToken(),
            $pushContext->getEndpointControlToken(),
            new PipelineRootStageContext($this->builder)
        );
    }
}
