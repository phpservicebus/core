<?php

namespace spec\PSB\Core\Serialization\Json;

use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Exception\JsonSerializerException;
use PSB\Core\Serialization\Json\JsonSerializer;
use spec\PSB\Core\Serialization\Json\JsonSerializerSpec\AllVisibilitiesClass;
use spec\PSB\Core\Serialization\Json\JsonSerializerSpec\EmptyClass;
use spec\PSB\Core\Serialization\Json\JsonSerializerSpec\MagicClass;

/**
 * @mixin JsonSerializer
 */
class JsonSerializerSpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Serialization\Json\JsonSerializer');
    }

    function it_serializes_a_scalar()
    {
        $scalarToJson = $this->getScalarsToJson();
        foreach ($scalarToJson as $pair) {
            $this->serialize($pair[0])->shouldBe($pair[1]);
        }
    }

    function it_deserializes_a_scalar()
    {
        $scalarToJson = $this->getScalarsToJson();
        foreach ($scalarToJson as $pair) {
            $this->unserialize($pair[1])->shouldBe($pair[0]);
        }
    }

    private function getScalarsToJson()
    {
        return [
            ['testing', '"testing"'],
            [123, '123'],
            [0, '0'],
            [0.0, '0.0'],
            [17.0, '17.0'],
            [17e1, '170.0'],
            [17.2, '17.2'],
            [true, 'true'],
            [false, 'false'],
            [null, 'null'],
            ['ßåö', '"ßåö"']
        ];
    }

    function it_serializes_float_localized()
    {
        $possibleLocales = ['fr_FR', 'fr_FR.utf8', 'fr', 'fra', 'French'];
        $originalLocale = setlocale(LC_NUMERIC, 0);
        if (!setlocale(LC_NUMERIC, $possibleLocales)) {
            throw new SkippingException("Unable to set an i18n locale.");
        }

        $this->serialize([1.0, 1.1, 0.00000000001, 1.999999999999, 223423.123456789, 1e5, 1e11])->shouldBe(
            '[1.0,1.1,1.0e-11,1.999999999999,223423.12345679,100000.0,100000000000.0]'
        );

        setlocale(LC_NUMERIC, $originalLocale);
    }

    function it_throws_when_serializing_resources()
    {
        $this->shouldThrow(JsonSerializerException::class)->duringSerialize(fopen(__FILE__, 'r'));
    }

    function it_throws_when_serializing_closures()
    {
        $this->shouldThrow(JsonSerializerException::class)->duringSerialize(
            [
                'func' => function () {
                    echo 'whoops';
                }
            ]
        );
    }

    function it_serializes_an_array_of_scalars_or_of_arrays_of_scalars()
    {
        $arrayToJson = $this->getArrayToJson();
        foreach ($arrayToJson as $pair) {
            $this->serialize($pair[0])->shouldBe($pair[1]);
        }
    }

    function it_unserializes_an_array_of_scalars_or_of_arrays_of_scalars()
    {
        $arrayToJson = $this->getArrayToJson();
        foreach ($arrayToJson as $pair) {
            $this->unserialize($pair[1])->shouldBe($pair[0]);
        }
    }

    private function getArrayToJson()
    {
        return [
            [[1, 2, 3], '[1,2,3]'],
            [[1, 'abc', false], '[1,"abc",false]'],
            [['a' => 1, 'b' => 2, 'c' => 3], '{"a":1,"b":2,"c":3}'],
            [['integer' => 1, 'string' => 'abc', 'bool' => false], '{"integer":1,"string":"abc","bool":false}'],
            [[1, ['nested']], '[1,["nested"]]'],
            [['integer' => 1, 'array' => ['nested']], '{"integer":1,"array":["nested"]}'],
            [['integer' => 1, 'array' => ['nested' => 'object']], '{"integer":1,"array":{"nested":"object"}}'],
            [[1.0, 2, 3e1], '[1.0,2,30.0]']
        ];
    }

    function it_serializes_a_stdclass()
    {
        $this->serialize(new \stdClass())->shouldBe('{"@type":"stdClass"}');
    }

    function it_unserializes_a_stdclass()
    {
        $this->unserialize('{"@type":"stdClass"}')->shouldHaveType(\stdClass::class);
    }

    function it_serializes_an_empty_class()
    {
        $this->serialize(new EmptyClass())->shouldBe(
            '{"@type":"spec\\\\PSB\\\\Core\\\\Serialization\\\\Json\\\\JsonSerializerSpec\\\\EmptyClass"}'
        );
    }

    function it_unserializes_an_empty_class()
    {
        $this->unserialize(
            '{"@type":"spec\\\\PSB\\\\Core\\\\Serialization\\\\Json\\\\JsonSerializerSpec\\\\EmptyClass"}'
        )->shouldHaveType(EmptyClass::class);
    }

    function it_serializes_a_class_with_members_of_any_visibility()
    {
        $obj = new AllVisibilitiesClass();
        $this->serialize($obj)->shouldBe(
            '{"@type":"spec\\\\PSB\\\\Core\\\\Serialization\\\\Json\\\\JsonSerializerSpec\\\\AllVisibilitiesClass","pub":"this is public","prot":"protected","priv":"dont tell anyone"}'
        );

        $obj->pub = 'new value';
        $this->serialize($obj)->shouldBe(
            '{"@type":"spec\\\\PSB\\\\Core\\\\Serialization\\\\Json\\\\JsonSerializerSpec\\\\AllVisibilitiesClass","pub":"new value","prot":"protected","priv":"dont tell anyone"}'
        );
    }

    function it_serializes_nested_classes()
    {
        $obj = new AllVisibilitiesClass();
        $nested = new EmptyClass();
        $obj->pub = $nested;
        $this->serialize($obj)->shouldBe(
            '{"@type":"spec\\\\PSB\\\\Core\\\\Serialization\\\\Json\\\\JsonSerializerSpec\\\\AllVisibilitiesClass","pub":{"@type":"spec\\\\PSB\\\\Core\\\\Serialization\\\\Json\\\\JsonSerializerSpec\\\\EmptyClass"},"prot":"protected","priv":"dont tell anyone"}'
        );
    }

    function it_unserializes_nested_classes()
    {
        $obj = $this->unserialize(
            '{"@type":"spec\\\\PSB\\\\Core\\\\Serialization\\\\Json\\\\JsonSerializerSpec\\\\AllVisibilitiesClass","pub":{"@type":"spec\\\\PSB\\\\Core\\\\Serialization\\\\Json\\\\JsonSerializerSpec\\\\EmptyClass"},"prot":"protected","priv":"dont tell anyone"}'
        );
        $obj->shouldHaveType(AllVisibilitiesClass::class);
        $obj->pub->shouldHaveType(EmptyClass::class);
        $obj->getProt()->shouldBe('protected');
        $obj->getPriv()->shouldBe('dont tell anyone');
    }

    function it_serializes_an_array_of_objects()
    {
        $this->serialize(['instance' => new EmptyClass()])->shouldBe(
            '{"instance":{"@type":"spec\\\\PSB\\\\Core\\\\Serialization\\\\Json\\\\JsonSerializerSpec\\\\EmptyClass"}}'
        );
    }

    function it_unserializes_an_array_of_objects()
    {
        $array = $this->unserialize(
            '{"instance":{"@type":"spec\\\\PSB\\\\Core\\\\Serialization\\\\Json\\\\JsonSerializerSpec\\\\EmptyClass"}}'
        );
        $array->shouldBeArray();
        $array['instance']->shouldHaveType(EmptyClass::class);
    }

    function it_serializes_an_object_with_dynamic_members()
    {
        $obj = new \stdClass();
        $obj->total = 10.0;
        $obj->discount = 0.0;
        $this->serialize($obj)->shouldBe('{"@type":"stdClass","total":10.0,"discount":0.0}');
    }

    function it_unserializes_an_object_with_dynamic_members()
    {
        $obj = $this->unserialize('{"@type":"stdClass","total":10.0,"discount":0.0}');
        $obj->shouldHaveType(\stdClass::class);
        $obj->total->shouldBe(10.0);
        $obj->discount->shouldBe(0.0);
    }

    function it_serializes_an_object_with_magic_methods()
    {
        $obj = new MagicClass();
        $this->serialize($obj)->shouldBe(
            '{"@type":"spec\\\\PSB\\\\Core\\\\Serialization\\\\Json\\\\JsonSerializerSpec\\\\MagicClass","show":true}'
        );
        expect($obj->woke)->toBe(false);
    }

    function it_unserializes_an_object_with_magic_methods()
    {
        $obj = $this->unserialize(
            '{"@type":"spec\\\\PSB\\\\Core\\\\Serialization\\\\Json\\\\JsonSerializerSpec\\\\MagicClass","show":true}'
        );
        $obj->woke->shouldBe(true);
    }

    function it_serializes_date_time()
    {
        $date = new \DateTime('2014-06-15 12:00:00', new \DateTimeZone('UTC'));
        $obj = $this->unserialize($this->serialize($date)->getWrappedObject());
        $obj->getTimestamp()->shouldBe($date->getTimestamp());
    }

    function it_throws_when_unserializing_an_unknown_class()
    {
        $this->shouldThrow(JsonSerializerException::class)->duringUnserialize('{"@type":"UnknownClass"}');
    }

    function it_serializes_cyclic_references()
    {
        $c1 = new \stdClass();
        $c1->c2 = new \stdClass();
        $c1->c2->c3 = new \stdClass();
        $c1->c2->c3->c1 = $c1;
        $c1->something = 'ok';
        $c1->c2->c3->ok = true;

        $this->serialize($c1)->shouldBe(
            '{"@type":"stdClass","c2":{"@type":"stdClass","c3":{"@type":"stdClass","c1":{"@type":"@0"},"ok":true}},"something":"ok"}'
        );

        $c1 = new \stdClass();
        $c1->mirror = $c1;
        $this->serialize($c1)->shouldBe('{"@type":"stdClass","mirror":{"@type":"@0"}}');
    }

    function it_unserializes_cyclic_references()
    {
        $obj = $this->unserialize(
            '{"@type":"stdClass","c2":{"@type":"stdClass","c3":{"@type":"stdClass","c1":{"@type":"@0"},"ok":true}},"something":"ok"}'
        );
        $obj->c2->c3->ok->shouldBe(true);
        $obj->c2->c3->c1->shouldBe($obj);
        $obj->c2->shouldNotBe($obj);

        $obj = $this->unserialize(
            '{"@type":"stdClass","c2":{"@type":"stdClass","c3":{"@type":"stdClass","c1":{"@type":"@0"},"c2":{"@type":"@1"},"c3":{"@type":"@2"}},"c3_copy":{"@type":"@2"}}}'
        );
        $obj->c2->c3->c1->shouldBe($obj);
        $obj->c2->c3->c2->shouldBe($obj->c2);
        $obj->c2->c3->c3->shouldBe($obj->c2->c3);
        $obj->c2->c3->shouldBe($obj->c2->c3_copy);
    }

    function it_throws_when_unserializing_invalid_json()
    {
        $this->shouldThrow(JsonSerializerException::class)->duringUnserialize('[this is not a valid json!}');
    }

    function it_throws_when_serializing_invalid_data()
    {
        $this->shouldThrow(JsonSerializerException::class)->duringSerialize([NAN]);
    }

    function it_serializes_binary_string_scalar()
    {
        $data = '';
        for ($i = 0; $i <= 255; $i++) {
            $data .= chr($i);
        }

        $this->unserialize($this->serialize($data)->getWrappedObject())->shouldBe($data);
    }

    function it_serializes_array_with_binary_strings_as_values()
    {
        $data = '';
        for ($i = 0; $i <= 255; $i++) {
            $data .= chr($i);
        }

        $this->unserialize($this->serialize([$data, "$data 1", "$data 2"])->getWrappedObject())->shouldBe(
            [$data, "$data 1", "$data 2"]
        );
    }

    /**
     * Starting from 1 and not from 0 because php cannot handle the nil character (\u0000) in json keys as per:
     * https://github.com/remicollet/pecl-json-c/issues/7
     * https://github.com/json-c/json-c/issues/108
     */
    function it_serializes_array_with_binary_strings_as_keys()
    {
        $data = '';
        for ($i = 1; $i <= 255; $i++) {
            $data .= chr($i);
        }

        $this->unserialize($this->serialize([$data => $data, "$data 1" => 'something'])->getWrappedObject())->shouldBe(
            [$data => $data, "$data 1" => 'something']
        );
    }

    function it_serializes_object_with_binary_strings()
    {
        $data = '';
        for ($i = 0; $i <= 255; $i++) {
            $data .= chr($i);
        }

        $obj = new \stdClass();
        $obj->string = $data;
        $this->unserialize($this->serialize($obj)->getWrappedObject())->shouldBeLike($obj);
    }
}

namespace spec\PSB\Core\Serialization\Json\JsonSerializerSpec;

class EmptyClass
{
}

class AllVisibilitiesClass
{
    public $pub = 'this is public';

    protected $prot = 'protected';

    private $priv = 'dont tell anyone';

    public function getProt()
    {
        return $this->prot;
    }

    public function getPriv()
    {
        return $this->priv;
    }
}

class MagicClass
{

    public $show = true;

    public $hide = true;

    public $woke = false;

    public function __sleep()
    {
        return array('show');
    }

    public function __wakeup()
    {
        $this->woke = true;
    }
}
