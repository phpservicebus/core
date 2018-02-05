<?php

namespace spec\PSB\Core\Persistence\PersistenceDefinitionApplierSpec;


class GodAwfulCallable
{
    public $invoked = false;

    public function __invoke($param)
    {
        $this->invoked = true;
    }
}
