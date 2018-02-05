<?php

namespace spec\PSB\Core\Serialization\Json;

use PhpSpec\ObjectBehavior;

use PSB\Core\Serialization\Json\JsonSerializationConfigurator;
use PSB\Core\Serialization\Json\JsonSerializationDefinition;
use PSB\Core\Util\Settings;

/**
 * @mixin JsonSerializationDefinition
 */
class JsonSerializationDefinitionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Serialization\Json\JsonSerializationDefinition');
    }

    function it_creates_a_configurator(Settings $settings)
    {
        $this->createConfigurator($settings)->shouldHaveType(JsonSerializationConfigurator::class);
    }

    function it_formalizez_by_returning_a_callable(Settings $settings)
    {
        $this->formalize($settings)->shouldHaveType(\Closure::class);
    }
}
