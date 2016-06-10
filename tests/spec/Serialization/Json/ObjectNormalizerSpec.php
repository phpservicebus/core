<?php

namespace spec\PSB\Core\Serialization\Json;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Exception\JsonSerializerException;
use PSB\Core\Serialization\Json\ObjectNormalizer;
use spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\AllVisibilitiesClass;
use spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\EmptyClass;
use spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\MagicClass;

/**
 * @mixin ObjectNormalizer
 */
class ObjectNormalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('@type');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Serialization\Json\ObjectNormalizer');
    }

    function it_throws_when_normalizing_non_object()
    {
        $this->shouldThrow(JsonSerializerException::class)->duringNormalize('');
        $this->shouldThrow(JsonSerializerException::class)->duringNormalize(5);
        $this->shouldThrow(JsonSerializerException::class)->duringNormalize([]);
    }

    function it_normalizes_a_stdclass()
    {
        $this->normalize(new \stdClass())->shouldReturn(['@type' => 'stdClass']);
    }

    function it_denormalizes_a_stdclass()
    {
        $this->denormalize(['@type' => 'stdClass'])->shouldHaveType(\stdClass::class);
    }

    function it_normalizes_an_empty_class()
    {
        $this->normalize(new EmptyClass())->shouldReturn(
            ['@type' => 'spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\EmptyClass']
        );
    }

    function it_denormalizes_an_empty_class()
    {
        $this->denormalize(
            ['@type' => 'spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\EmptyClass']
        )->shouldHaveType(EmptyClass::class);
    }

    function it_normalizes_a_class_with_members_of_any_visibility()
    {
        $obj = new AllVisibilitiesClass();
        $this->normalize($obj)->shouldReturn(
            [
                '@type' => 'spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\AllVisibilitiesClass',
                'pub' => 'this is public',
                'prot' => 'protected',
                'priv' => 'dont tell anyone'
            ]
        );

        $obj->pub = 'new value';
        $this->normalize($obj)->shouldReturn(
            [
                '@type' => 'spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\AllVisibilitiesClass',
                'pub' => 'new value',
                'prot' => 'protected',
                'priv' => 'dont tell anyone'
            ]
        );
    }

    function it_normalizes_nested_classes()
    {
        $obj = new AllVisibilitiesClass();
        $nested = new EmptyClass();
        $obj->pub = $nested;
        $this->normalize($obj)->shouldReturn(
            [
                '@type' => 'spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\AllVisibilitiesClass',
                'pub' => ['@type' => 'spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\EmptyClass'],
                'prot' => 'protected',
                'priv' => 'dont tell anyone'
            ]
        );
    }

    function it_denormalizes_nested_classes()
    {
        $obj = $this->denormalize(
            [
                '@type' => 'spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\AllVisibilitiesClass',
                'pub' => ['@type' => 'spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\EmptyClass'],
                'prot' => 'protected',
                'priv' => 'dont tell anyone'
            ]
        );
        $obj->shouldHaveType(AllVisibilitiesClass::class);
        $obj->pub->shouldHaveType(EmptyClass::class);
        $obj->getProt()->shouldBe('protected');
        $obj->getPriv()->shouldBe('dont tell anyone');
    }

    function it_normalizes_an_object_with_dynamic_members()
    {
        $obj = new \stdClass();
        $obj->total = 10;
        $obj->discount = 5;
        $this->normalize($obj)->shouldReturn(['@type' => 'stdClass', 'total' => 10, 'discount' => 5]);
    }

    function it_denormalizes_an_object_with_dynamic_members()
    {
        $obj = $this->denormalize(['@type' => 'stdClass', 'total' => 10, 'discount' => 5]);
        $obj->shouldHaveType(\stdClass::class);
        $obj->total->shouldBe(10);
        $obj->discount->shouldBe(5);
    }

    function it_normalizes_an_object_with_magic_sleep_method()
    {
        $obj = new MagicClass();
        $this->normalize($obj)->shouldReturn(
            [
                '@type' => 'spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\MagicClass',
                'show' => true
            ]
        );
        expect($obj->woke)->toBe(false);
    }

    function it_denormalizes_an_object_with_magic_wakeup_method()
    {
        $obj = $this->denormalize(
            [
                '@type' => 'spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\MagicClass',
                'show' => true
            ]
        );

        $obj->woke->shouldBe(true);
    }

    function it_normalizes_date_time()
    {
        $date = new \DateTime('2014-06-15 12:00:00', new \DateTimeZone('UTC'));

        $normalized = $this->normalize($date);
        $normalized->shouldBeArray();
        $obj = $this->denormalize($normalized);
        $obj->getTimestamp()->shouldBe($date->getTimestamp());
    }

    function it_throws_when_denormalizing_an_unknown_class()
    {
        $this->shouldThrow(JsonSerializerException::class)->duringDenormalize(['@type' => 'UnknownClass']);
    }

    function it_normalizes_cyclic_references()
    {
        $c1 = new \stdClass();
        $c1->c2 = new \stdClass();
        $c1->c2->c1 = $c1;
        $c1->c2->c3 = new \stdClass();
        $c1->c2->c3->c1 = $c1;
        $c1->something = 'ok';
        $c1->c2->c3->ok = true;

        $this->normalize($c1)->shouldReturn(
            [
                '@type' => 'stdClass',
                'c2' => [
                    '@type' => 'stdClass',
                    'c1' => ['@type' => '@0'],
                    'c3' => ['@type' => 'stdClass', 'c1' => ['@type' => '@0'], 'ok' => true]
                ],
                'something' => 'ok'
            ]
        );
    }

    function it_denormalizes_cyclic_references()
    {
        $obj = $this->denormalize(
            [
                '@type' => 'stdClass',
                'c2' => [
                    '@type' => 'stdClass',
                    'c1' => ['@type' => '@0'],
                    'c3' => ['@type' => 'stdClass', 'c1' => ['@type' => '@0'], 'ok' => true]
                ],
                'something' => 'ok'
            ]
        );

        $obj->c2->c1->shouldBe($obj);
        $obj->c2->c3->c1->shouldBe($obj);
        $obj->c2->c3->ok->shouldBe(true);
        $obj->something->shouldBe('ok');
    }

    function it_normalizes_self_cyclic_references()
    {
        $c1 = new \stdClass();
        $c1->mirror = $c1;
        $this->normalize($c1)->shouldReturn(['@type' => 'stdClass', 'mirror' => ['@type' => '@0']]);
    }

    function it_denormalizes_self_cyclic_references()
    {
        $obj = $this->denormalize(['@type' => 'stdClass', 'mirror' => ['@type' => '@0']]);
        $obj->mirror->shouldBe($obj);
    }

    function it_normalizes_object_with_nested_array_of_objects()
    {
        $obj = new \stdClass();
        $obj->array = [new \stdClass(), new EmptyClass()];
        $this->normalize($obj)->shouldReturn(
            [
                '@type' => 'stdClass',
                'array' => [
                    ['@type' => 'stdClass'],
                    ['@type' => 'spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\EmptyClass']
                ]
            ]
        );
    }

    function it_denormalizes_object_with_nested_array_of_objects()
    {
        $obj = new \stdClass();
        $obj->array = [new \stdClass(), new EmptyClass()];
        $obj = $this->denormalize(
            [
                '@type' => 'stdClass',
                'array' => [
                    ['@type' => 'stdClass'],
                    ['@type' => 'spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec\EmptyClass']
                ]
            ]
        );
        $obj->shouldHaveType(\stdClass::class);
        $obj->array->shouldBeArray();
        $obj->array[0]->shouldHaveType(\stdClass::class);
        $obj->array[1]->shouldHaveType(EmptyClass::class);
    }
}

namespace spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec;

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
        return ['show'];
    }

    public function __wakeup()
    {
        $this->woke = true;
    }
}
