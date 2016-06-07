<?php
namespace PSB\Core\Transport;


use PSB\Core\Pipeline\PipelineInterface;

class PushPipe
{
    /**
     * @var TransportReceiveContextFactory
     */
    private $contextFactory;

    /**
     * @var PipelineInterface
     */
    private $pipeline;

    /**
     * @param TransportReceiveContextFactory $contextFactory
     * @param PipelineInterface              $pipeline
     */
    public function __construct(TransportReceiveContextFactory $contextFactory, PipelineInterface $pipeline)
    {
        $this->contextFactory = $contextFactory;
        $this->pipeline = $pipeline;
    }

    /**
     * @param PushContext $pushContext
     */
    public function push(PushContext $pushContext)
    {
        $transportReceiveContext = $this->contextFactory->createFromPushContext($pushContext);

        $this->pipeline->invoke($transportReceiveContext);
    }
}
