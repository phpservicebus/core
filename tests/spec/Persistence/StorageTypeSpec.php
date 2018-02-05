<?php

namespace spec\PSB\Core\Persistence;

use PhpSpec\ObjectBehavior;
use PSB\Core\Exception\UnexpectedValueException;
use PSB\Core\Persistence\StorageType;
use spec\PSB\Core\Persistence\StorageTypeSpec\AnotherStorageType;

/**
 * @mixin StorageType
 */
class StorageTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(StorageType::OUTBOX);
    }

    function it_is_initializable_given_a_valid_value()
    {
        $this->beConstructedWith(StorageType::OUTBOX);
        $this->shouldHaveType('PSB\Core\Persistence\StorageType');
    }

    function it_throws_if_constructed_with_an_invalid_value()
    {
        $this->beConstructedWith('whatever');
        $this->shouldThrow(UnexpectedValueException::class)->duringInstantiation();
    }

    function it_can_be_constructed_through_static_method()
    {
        self::OUTBOX()->shouldBeLike(new StorageType(StorageType::OUTBOX));
    }

    function it_can_provide_the_available_constants()
    {
        self::getConstants()->shouldReturn(['OUTBOX' => StorageType::OUTBOX]);
    }

    function it_returns_its_value()
    {
        $this->getValue()->shouldReturn(StorageType::OUTBOX);
    }

    function it_equals_another_storage_type_with_the_same_value()
    {
        $this->equals(StorageType::OUTBOX())->shouldBe(true);
    }

    function it_does_not_equal_another_storage_type_with_a_different_value()
    {
        $this->equals(AnotherStorageType::WHATEVER())->shouldBe(false);
    }
}
