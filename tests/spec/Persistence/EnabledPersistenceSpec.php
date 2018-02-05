<?php

namespace spec\PSB\Core\Persistence;

use PhpSpec\ObjectBehavior;
use PSB\Core\Persistence\EnabledPersistence;
use PSB\Core\Persistence\StorageType;
use spec\PSB\Core\Persistence\EnabledPersistenceSpec\TestDefinition;

/**
 * @mixin EnabledPersistence
 */
class EnabledPersistenceSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(new TestDefinition());
        $this->shouldHaveType('PSB\Core\Persistence\EnabledPersistence');
    }

    function it_contains_the_class_name_set_at_construction(TestDefinition $definition)
    {
        $this->beConstructedWith($definition);
        $this->getDefinition()->shouldReturn($definition);
    }

    function it_contains_the_storage_type_set_at_construction()
    {
        $this->beConstructedWith(new TestDefinition(), StorageType::OUTBOX());
        $this->getSelectedStorageType()->shouldBeLike(StorageType::OUTBOX());
    }
}
