<?php
namespace PSB\Core\Serialization\Json;


use PSB\Core\ContentTypeEnum;
use PSB\Core\Serialization\MessageSerializerInterface;

class JsonMessageSerializer implements MessageSerializerInterface
{
    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @param JsonSerializer $serializer
     */
    public function __construct(JsonSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Serializes an object or an array of objects into a string
     *
     * @param object $message
     *
     * @return string
     */
    public function serialize($message)
    {
        return $this->serializer->serialize($message);
    }

    /**
     * Deserializes a set of messages from the given string
     *
     * @param string $string
     * @param string $messageType
     *
     * @return object
     */
    public function deserialize($string, $messageType)
    {
        return $this->serializer->unserialize($string);
    }

    /**
     * Returns the content type handled by this serializer
     *
     * @return string
     */
    public function getContentType()
    {
        return ContentTypeEnum::JSON;
    }
}
