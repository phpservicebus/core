<?php

namespace spec\PSB\Core\Serialization\Json;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\ContentTypeEnum;
use PSB\Core\Serialization\Json\JsonMessageSerializer;
use PSB\Core\Serialization\Json\JsonSerializer;

/**
 * @mixin JsonMessageSerializer
 */
class JsonMessageSerializerSpec extends ObjectBehavior
{
    /**
     * @var JsonSerializer
     */
    private $serializerMock;

    function let(JsonSerializer $serializer)
    {
        $this->serializerMock = $serializer;
        $this->beConstructedWith($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Serialization\Json\JsonMessageSerializer');
    }

    function it_serializes($message, $serializedMessage)
    {
        $this->serializerMock->serialize($message)->willReturn($serializedMessage);
        $this->serialize($message)->shouldReturn($serializedMessage);
    }

    function it_deserializes($serializedMessage, $messageType, $unserializedMessage)
    {
        $this->serializerMock->unserialize($serializedMessage)->willReturn($unserializedMessage);
        $this->deserialize($serializedMessage, $messageType)->shouldReturn($unserializedMessage);
    }

    function it_return_the_json_content_type()
    {
        $this->getContentType()->shouldReturn(ContentTypeEnum::JSON);
    }
}
