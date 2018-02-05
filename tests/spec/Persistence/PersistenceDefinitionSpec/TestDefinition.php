<?php

namespace spec\PSB\Core\Persistence\PersistenceDefinitionSpec;


use PSB\Core\Persistence\PersistenceDefinition;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Util\Settings;

class TestDefinition extends PersistenceDefinition
{
    private $callable;

    public function createConfigurator(Settings $settings)
    {
    }

    public function formalize()
    {
        $this->supports(StorageType::OUTBOX(), $this->callable);
    }

    public function setCallable(callable $callable)
    {
        $this->callable = $callable;
    }
}
