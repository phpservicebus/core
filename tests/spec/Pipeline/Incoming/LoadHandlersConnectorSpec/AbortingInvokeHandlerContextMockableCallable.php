<?php

namespace spec\PSB\Core\Pipeline\Incoming\LoadHandlersConnectorSpec;


use PSB\Core\Pipeline\Incoming\StageContext\InvokeHandlerContext;

class AbortingInvokeHandlerContextMockableCallable
{
    public function __invoke(InvokeHandlerContext $context)
    {
        $context->doNotContinueDispatchingCurrentMessageToHandlers();
        $this->invoke($context);
    }

    public function invoke(InvokeHandlerContext $context)
    {

    }
}
