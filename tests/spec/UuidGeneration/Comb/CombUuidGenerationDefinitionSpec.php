<?php

namespace spec\PSB\Core\UuidGeneration\Comb;

use PhpSpec\ObjectBehavior;

use PSB\Core\Util\Settings;
use PSB\Core\UuidGeneration\Comb\CombUuidGenerationConfigurator;
use PSB\Core\UuidGeneration\Comb\CombUuidGenerationDefinition;

/**
 * @mixin CombUuidGenerationDefinition
 */
class CombUuidGenerationDefinitionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\UuidGeneration\Comb\CombUuidGenerationDefinition');
    }

    function it_creates_a_configurator(Settings $settings)
    {
        $this->createConfigurator($settings)->shouldHaveType(CombUuidGenerationConfigurator::class);
    }

    function it_formalizez_by_returning_a_callable(Settings $settings)
    {
        $this->formalize($settings)->shouldHaveType('\Closure');
    }
}
