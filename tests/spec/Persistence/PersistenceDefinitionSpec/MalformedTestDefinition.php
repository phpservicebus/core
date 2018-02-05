<?php

namespace spec\PSB\Core\Persistence\PersistenceDefinitionSpec;


use PSB\Core\Persistence\PersistenceDefinition;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Util\Settings;

class MalformedTestDefinition extends PersistenceDefinition
{
    public function createConfigurator(Settings $settings)
    {
    }

    public function formalize()
    {
        $this->supports(
            StorageType::OUTBOX(),
            function () {
            }
        );

        $this->supports(
            StorageType::OUTBOX(),
            function () {
            }
        );
    }
}
