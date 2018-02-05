<?php

namespace spec\PSB\Core\Pipeline\Incoming\LoadHandlersConnectorSpec;


use PSB\Core\Pipeline\Incoming\StageContext\InvokeHandlerContext;

class InvokeHandlerContextMockableCallable
{
    public function __invoke(InvokeHandlerContext $context)
    {
        $this->invoke($context);
    }

    public function invoke(InvokeHandlerContext $context)
    {

    }
}
