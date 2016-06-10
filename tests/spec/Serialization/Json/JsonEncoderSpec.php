<?php

namespace spec\PSB\Core\Serialization\Json;

use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Exception\JsonSerializerException;
use PSB\Core\Serialization\Json\JsonEncoder;

/**
 * @mixin JsonEncoder
 */
class JsonEncoderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(JSON_UNESCAPED_UNICODE, '@utf8encoded');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Serialization\Json\JsonEncoder');
    }

    function it_encodes_an_array_as_a_json_string()
    {
        $this->encode(['key' => 'value', 'value2', 12])->shouldReturn('{"key":"value","0":"value2","1":12}');
    }

    function it_decodes_an_array_from_a_json_string()
    {
        $this->decode('{"key":"value","0":"value2","1":12}')->shouldReturn(['key' => 'value', 'value2', 12]);
    }

    function it_throws_when_decoding_an_invalid_json()
    {
        $this->shouldThrow(JsonSerializerException::class)->duringDecode('[Invalid JSON]');
    }

    function it_throws_when_encoding_an_array_containing_nan()
    {
        $this->shouldThrow(JsonSerializerException::class)->duringEncode([NAN]);
    }

    function it_encodes_array_with_binary_strings_as_values()
    {
        $data = '';
        for ($i = 0; $i <= 255; $i++) {
            $data .= chr($i);
        }
        $this->decode($this->encode([$data, "$data 1", "$data 2"]))->shouldReturn(
            [$data, "$data 1", "$data 2"]
        );
    }

    /**
     * Starting from 1 and not from 0 because php cannot handle the nil character (\u0000) in json keys as per:
     * https://github.com/remicollet/pecl-json-c/issues/7
     * https://github.com/json-c/json-c/issues/108
     */
    function it_encodes_array_with_binary_strings_as_keys()
    {
        $data = '';
        for ($i = 1; $i <= 255; $i++) {
            $data .= chr($i);
        }

        $this->decode($this->encode([$data => $data, "$data 1" => 'something']))->shouldReturn(
            [$data => $data, "$data 1" => 'something']
        );
    }

    function it_encodes_unaffected_by_float_localization()
    {
        $possibleLocales = ['fr_FR', 'fr_FR.utf8', 'fr', 'fra', 'French'];
        $originalLocale = setlocale(LC_NUMERIC, 0);
        if (!setlocale(LC_NUMERIC, $possibleLocales)) {
            throw new SkippingException("Unable to set an i18n locale.");
        }

        $this->encode([1.0, 1.1, 0.00000000001, 1.999999999999, 223423.123456789, 1e5, 1e11])->shouldReturn(
            '[1.0,1.1,1.0e-11,1.999999999999,223423.12345679,100000.0,100000000000.0]'
        );

        setlocale(LC_NUMERIC, $originalLocale);
    }

    function it_encodes_zero_fraction_floats_even_without_library_support()
    {
        if (defined('JSON_PRESERVE_ZERO_FRACTION')) {
            throw new SkippingException("Library has zero fraction support.");
        }

        $this->encode([10.0, 0.0])->shouldReturn('[10.0,0.0]');
    }
}
