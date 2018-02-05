<?php

namespace spec\PSB\Core\Persistence\PersistenceDefinitionApplierSpec;


use PSB\Core\Persistence\PersistenceDefinition;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Util\Settings;

class TestDefinition extends PersistenceDefinition
{
    public static $storageTypeInitializer;

    public function formalize()
    {
        $this->supports(StorageType::OUTBOX(), static::$storageTypeInitializer);
    }


    public function createConfigurator(Settings $settings)
    {
    }
}
