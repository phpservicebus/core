<?php

namespace spec\PSB\Core\Persistence;

use PhpSpec\ObjectBehavior;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\Persistence\EnabledPersistence;
use PSB\Core\Persistence\PersistenceDefinitionApplier;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Util\Settings;
use spec\PSB\Core\Persistence\PersistenceDefinitionApplierSpec\GodAwfulCallable;
use spec\PSB\Core\Persistence\PersistenceDefinitionApplierSpec\SupportsNothingDefinition;
use spec\PSB\Core\Persistence\PersistenceDefinitionApplierSpec\TestDefinition;

/**
 * @mixin PersistenceDefinitionApplier
 */
class PersistenceDefinitionApplierSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Persistence\PersistenceDefinitionApplier');
    }

    function it_applies_enabled_definitions_and_saves_the_supported_storage_types_in_the_settings(Settings $settings)
    {
        $storageTypeInitializer = new GodAwfulCallable();
        TestDefinition::$storageTypeInitializer = $storageTypeInitializer;
        $settings->tryGet(KnownSettingsEnum::ENABLED_PERSISTENCES)->willReturn(
            [
                new EnabledPersistence(new TestDefinition(), StorageType::OUTBOX())
            ]
        );

        $settings->set(KnownSettingsEnum::SUPPORTED_STORAGE_TYPE_VALUES, ['Outbox' => 'Outbox'])->shouldBeCalled();

        $this->apply($settings);

        expect($storageTypeInitializer->invoked)->toBe(true);
    }

    function it_throws_if_there_are_no_enabled_persistences_in_settings(Settings $settings)
    {
        $settings->tryGet(KnownSettingsEnum::ENABLED_PERSISTENCES)->willReturn(null);

        $this->shouldThrow('PSB\Core\Exception\UnexpectedValueException')->duringApply($settings);
    }

    function it_throws_if_a_definition_does_not_support_the_enabled_storage_type(Settings $settings)
    {
        $settings->tryGet(KnownSettingsEnum::ENABLED_PERSISTENCES)->willReturn(
            [
                new EnabledPersistence(new SupportsNothingDefinition(), StorageType::OUTBOX())
            ]
        );

        $this->shouldThrow('PSB\Core\Exception\RuntimeException')->duringApply($settings);
    }
}
