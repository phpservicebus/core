<?php
namespace PSB\Core\Pipeline\Incoming;


use PSB\Core\Pipeline\Incoming\StageContext\InvokeHandlerContext;
use PSB\Core\Pipeline\PipelineTerminator;

class InvokeHandlerTerminator extends PipelineTerminator
{

    /**
     * @param InvokeHandlerContext $context
     *
     * @return void
     */
    protected function terminate($context)
    {
        $handler = $context->getMessageHandler();
        $handler->handle($context->getMessageBeingHandled(), $context);
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return InvokeHandlerContext::class;
    }

    /**
     * @return string
     */
    public static function getNextStageContextClass()
    {
        return '';
    }
}
