<?php

namespace spec\PSB\Core\Serialization\MessageDeserializerResolverSpec;


use PSB\Core\Serialization\MessageSerializerInterface;

class XmlSerializer implements MessageSerializerInterface
{
    public function serialize($message)
    {
    }

    public function deserialize($string, $messageType)
    {
    }

    public function getContentType()
    {
    }
}
