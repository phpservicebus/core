<?php

namespace spec\PSB\Core\Serialization;

use PhpSpec\ObjectBehavior;
use PSB\Core\ContentTypeEnum;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\Serialization\MessageDeserializerResolver;
use spec\PSB\Core\Serialization\MessageDeserializerResolverSpec\JsonSerializer;
use spec\PSB\Core\Serialization\MessageDeserializerResolverSpec\XmlSerializer;

/**
 * @mixin MessageDeserializerResolver
 */
class MessageDeserializerResolverSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([], '');
        $this->shouldHaveType('PSB\Core\Serialization\MessageDeserializerResolver');
    }

    function it_resolves_to_default_if_no_serializer_found_for_content_type(
        JsonSerializer $jsonSerializer,
        XmlSerializer $xmlSerializer
    ) {
        $jsonSerializer->getContentType()->willReturn(ContentTypeEnum::JSON);
        $xmlSerializer->getContentType()->willReturn(ContentTypeEnum::XML);
        $this->beConstructedWith(
            [$xmlSerializer, $jsonSerializer],
            'spec\PSB\Core\Serialization\MessageDeserializerResolverSpec\JsonSerializer'
        );

        $this->resolve([HeaderTypeEnum::CONTENT_TYPE => 'bson'])->shouldReturn($jsonSerializer);
    }

    function it_resolves_to_default_if_content_type_not_defined(
        JsonSerializer $jsonSerializer,
        XmlSerializer $xmlSerializer
    ) {
        $jsonSerializer->getContentType()->willReturn(ContentTypeEnum::JSON);
        $xmlSerializer->getContentType()->willReturn(ContentTypeEnum::XML);
        $this->beConstructedWith(
            [$xmlSerializer, $jsonSerializer],
            'spec\PSB\Core\Serialization\MessageDeserializerResolverSpec\JsonSerializer'
        );

        $this->resolve([])->shouldReturn($jsonSerializer);
    }

    function it_resolves_to_the_first_serializer_that_matches_the_content_type(
        JsonSerializer $jsonSerializer,
        XmlSerializer $xmlSerializer
    ) {
        $jsonSerializer->getContentType()->willReturn(ContentTypeEnum::JSON);
        $xmlSerializer->getContentType()->willReturn(ContentTypeEnum::XML);
        $this->beConstructedWith(
            [$xmlSerializer, $jsonSerializer],
            'spec\PSB\Core\Serialization\MessageDeserializerResolverSpec\JsonSerializer'
        );

        $this->resolve([HeaderTypeEnum::CONTENT_TYPE => ContentTypeEnum::XML])->shouldReturn($xmlSerializer);
    }

    function it_throws_if_default_serializer_is_not_found(XmlSerializer $xmlSerializer)
    {
        $xmlSerializer->getContentType()->willReturn(ContentTypeEnum::XML);
        $this->beConstructedWith(
            [$xmlSerializer],
            'spec\PSB\Core\Serialization\MessageDeserializerResolverSpec\JsonSerializer'
        );
        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }
}
