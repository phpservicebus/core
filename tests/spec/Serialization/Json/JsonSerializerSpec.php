<?php

namespace spec\PSB\Core\Serialization\Json;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Serialization\Json\JsonEncoder;
use PSB\Core\Serialization\Json\JsonSerializer;
use PSB\Core\Serialization\Json\ObjectNormalizer;

/**
 * @mixin JsonSerializer
 */
class JsonSerializerSpec extends ObjectBehavior
{
    /**
     * @var ObjectNormalizer
     */
    private $normalizerMock;

    /**
     * @var JsonEncoder
     */
    private $encoderMock;

    function let(ObjectNormalizer $normalizer, JsonEncoder $encoder)
    {
        $this->normalizerMock = $normalizer;
        $this->encoderMock = $encoder;
        $this->beConstructedWith($normalizer, $encoder);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Serialization\Json\JsonSerializer');
    }

    function it_serializes_by_normalizing_and_encoding($someObject)
    {
        $this->normalizerMock->normalize($someObject)->willReturn(['normalized']);
        $this->encoderMock->encode(['normalized'])->willReturn('encoded');
        $this->serialize($someObject)->shouldReturn('encoded');
    }

    function it_unserialzies_by_decoding_and_denormalizing($someObject)
    {
        $this->encoderMock->decode('encoded')->willReturn(['decoded']);
        $this->normalizerMock->denormalize(['decoded'])->willReturn($someObject);
        $this->unserialize('encoded')->shouldReturn($someObject);
    }
}

