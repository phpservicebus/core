<?php

namespace spec\PSB\Core\Persistence\PersistenceDefinitionApplierSpec;


use PSB\Core\Persistence\PersistenceDefinition;
use PSB\Core\Util\Settings;

class SupportsNothingDefinition extends PersistenceDefinition
{
    public function formalize()
    {
    }

    public function createConfigurator(Settings $settings)
    {
    }
}
