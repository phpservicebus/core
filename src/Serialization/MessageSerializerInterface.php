<?php
namespace PSB\Core\Serialization;


interface MessageSerializerInterface
{
    /**
     * Serializes an object or an array of objects into a string
     *
     * @param object $message
     *
     * @return string
     */
    public function serialize($message);

    /**
     * Deserializes a set of messages from the given string
     *
     * @param string $string
     * @param string $messageType
     *
     * @return object
     */
    public function deserialize($string, $messageType);

    /**
     * Returns the content type handled by this serializer
     *
     * @return string
     */
    public function getContentType();
}
