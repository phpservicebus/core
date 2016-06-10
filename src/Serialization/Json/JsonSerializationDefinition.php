<?php
namespace PSB\Core\Serialization\Json;


use PSB\Core\Serialization\SerializationConfigurator;
use PSB\Core\Serialization\SerializationDefinition;
use PSB\Core\Util\Settings;

class JsonSerializationDefinition extends SerializationDefinition
{

    /**
     * Creates the SerializationConfigurator specific to the serialization implementation.
     *
     * @param Settings $settings
     *
     * @return SerializationConfigurator
     */
    public function createConfigurator(Settings $settings)
    {
        return new JsonSerializationConfigurator($settings);
    }

    /**
     * Provides a factory method for building a message serializer
     *
     * @param Settings $settings
     *
     * @return callable
     */
    public function formalize(Settings $settings)
    {
        return function () {
            return new JsonMessageSerializer(new JsonSerializer(new ObjectNormalizer(), new JsonEncoder()));
        };
    }
}
