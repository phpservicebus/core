<?php
namespace PSB\Core\Persistence;


use PSB\Core\Exception\RuntimeException;
use PSB\Core\Exception\UnexpectedValueException;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\Util\Settings;

class PersistenceDefinitionApplier
{
    public function apply(Settings $settings)
    {
        /** @var EnabledPersistence[]|null $enabledPersistences */
        $enabledPersistences = $settings->tryGet(KnownSettingsEnum::ENABLED_PERSISTENCES);

        if (!$enabledPersistences) {
            // TODO tweak the error message (vezi interfetele de care vb in NServicebus)
            // TODO vezi si de SendOnly endpoint cand lamuresti cu sending messages din afara unui handler (fara tranzactie explicita)
            throw new UnexpectedValueException(
                "No persistence has been selected, please select your persistence by calling endpointConfigurator.usePersistence."
            );
        }

        $enabledPersistences = array_reverse($enabledPersistences);
        $availableStorageTypeValues = array_flip(StorageType::getConstants());

        $supportedStorageTypeValues = [];

        foreach ($enabledPersistences as $enabledPersistence) {
            $currentDefinition = $enabledPersistence->getDefinition();
            $currentDefinition->formalize();
            $selectedStorageType = $enabledPersistence->getSelectedStorageType();

            if ($selectedStorageType && !$currentDefinition->hasSupportFor($selectedStorageType)) {
                $definitionFqcn = get_class($currentDefinition);
                throw new RuntimeException(
                    "Definition '$definitionFqcn' does not support storage type {$selectedStorageType->getValue()}."
                );
            }

            $currentSupportedStorageTypeValues = $currentDefinition->getSupportedStorages($selectedStorageType);
            foreach ($currentSupportedStorageTypeValues as $supportedStorageValue) {
                if (isset($availableStorageTypeValues[$supportedStorageValue])) {
                    unset($availableStorageTypeValues[$supportedStorageValue]);
                    $supportedStorageTypeValues[$supportedStorageValue] = $supportedStorageValue;
                    $currentDefinition->applyFor(new StorageType($supportedStorageValue), $settings);
                }
            }
        }

        $settings->set(KnownSettingsEnum::SUPPORTED_STORAGE_TYPE_VALUES, $supportedStorageTypeValues);
    }
}
