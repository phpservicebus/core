<?php
namespace PSB\Core\Serialization;


use PSB\Core\Exception\InvalidArgumentException;
use PSB\Core\HeaderTypeEnum;

class MessageDeserializerResolver
{
    /**
     * Serializers indexed by content type
     *
     * @var MessageSerializerInterface[]
     */
    private $messageSerializers = [];

    /**
     * @var MessageSerializerInterface
     */
    private $defaultSerializer;

    /**
     * @param MessageSerializerInterface[] $messageSerializers
     * @param string                       $defaultSerializerFqcn
     */
    public function __construct(array $messageSerializers, $defaultSerializerFqcn)
    {
        $defaultFound = false;
        foreach ($messageSerializers as $serializer) {
            if ($serializer instanceof $defaultSerializerFqcn) {
                $this->defaultSerializer = $serializer;
                $defaultFound = true;
                break;
            }
        }

        if (!$defaultFound) {
            throw new InvalidArgumentException("Found no serializer that matches class '$defaultSerializerFqcn'.");
        }

        foreach ($messageSerializers as $serializer) {
            $this->messageSerializers[$serializer->getContentType()] = $serializer;
        }
    }

    /**
     * @param array $headers
     *
     * @return MessageSerializerInterface
     */
    public function resolve(array $headers)
    {
        if (!isset($headers[HeaderTypeEnum::CONTENT_TYPE]) ||
            !isset($this->messageSerializers[$headers[HeaderTypeEnum::CONTENT_TYPE]])
        ) {
            return $this->defaultSerializer;
        }

        return $this->messageSerializers[$headers[HeaderTypeEnum::CONTENT_TYPE]];
    }
}
