<?php
namespace PSB\Core\UuidGeneration\Comb;


use PSB\Core\Util\Settings;
use PSB\Core\UuidGeneration\UuidGenerationDefinition;

class CombUuidGenerationDefinition extends UuidGenerationDefinition
{
    /**
     * Creates the UuidGenerationConfigurator specific to the uuid generation implementation.
     *
     * @param Settings $settings
     *
     * @return CombUuidGenerationConfigurator
     */
    public function createConfigurator(Settings $settings)
    {
        return new CombUuidGenerationConfigurator($settings);
    }

    /**
     * Provides a factory method for building a uuid generator
     *
     * @param Settings $settings
     *
     * @return callable
     */
    public function formalize(Settings $settings)
    {
        return function () {
            return new TimestampFirstCombGenerator();
        };
    }
}
