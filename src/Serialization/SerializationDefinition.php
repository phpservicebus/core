<?php
namespace PSB\Core\Serialization;


use PSB\Core\Util\Settings;

abstract class SerializationDefinition
{
    /**
     * Creates the SerializationConfigurator specific to the serialization implementation.
     *
     * @param Settings $settings
     *
     * @return SerializationConfigurator
     */
    abstract public function createConfigurator(Settings $settings);

    /**
     * Provides a factory method for building a message serializer
     *
     * @param Settings $settings
     *
     * @return callable
     */
    abstract public function formalize(Settings $settings);
}
