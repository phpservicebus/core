<?php
namespace PSB\Core\Pipeline\Outgoing;


use PSB\Core\Pipeline\Outgoing\StageContext\DispatchContext;
use PSB\Core\Pipeline\PipelineTerminator;
use PSB\Core\Transport\MessageDispatcherInterface;
use PSB\Core\Transport\TransportOperations;

class ImmediateDispatchTerminator extends PipelineTerminator
{
    /**
     * @var MessageDispatcherInterface
     */
    private $messageDispatcher;

    /**
     * @param MessageDispatcherInterface $messageDispatcher
     */
    public function __construct(MessageDispatcherInterface $messageDispatcher)
    {
        $this->messageDispatcher = $messageDispatcher;
    }

    /**
     * @param DispatchContext $context
     */
    protected function terminate($context)
    {
        $this->messageDispatcher->dispatch(new TransportOperations($context->getTransportOperations()));
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return DispatchContext::class;
    }

    /**
     * @return string
     */
    public static function getNextStageContextClass()
    {
        return '';
    }
}
