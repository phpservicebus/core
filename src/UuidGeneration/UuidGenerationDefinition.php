<?php
namespace PSB\Core\UuidGeneration;


use PSB\Core\Util\Settings;

abstract class UuidGenerationDefinition
{
    /**
     * Creates the UuidGenerationConfigurator specific to the uuid generation implementation.
     *
     * @param Settings $settings
     *
     * @return UuidGenerationConfigurator
     */
    abstract public function createConfigurator(Settings $settings);

    /**
     * Provides a factory method for building a uuid generator
     *
     * @param Settings $settings
     *
     * @return callable
     */
    abstract public function formalize(Settings $settings);
}
