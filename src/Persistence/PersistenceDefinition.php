<?php
namespace PSB\Core\Persistence;


use PSB\Core\Exception\InvalidArgumentException;
use PSB\Core\Util\Settings;

abstract class PersistenceDefinition
{
    private $storageTypeToInitializerMap = [];

    /**
     * Creates the PersistenceConfigurator specific to the transport implementation.
     *
     * @param Settings $settings
     *
     * @return PersistenceConfigurator
     */
    abstract public function createConfigurator(Settings $settings);

    /**
     * This is where subclasses declare what storage types they support together with
     * the initializers for those types.
     */
    abstract public function formalize();

    /**
     * @param StorageType $storageType
     * @param callable    $storageTypeInitializer
     */
    protected function supports(StorageType $storageType, callable $storageTypeInitializer)
    {
        if (isset($this->storageTypeToInitializerMap[$storageType->getValue()])) {
            throw new InvalidArgumentException(
                "Storage type initializer for type '{$storageType->getValue()}' is already defined."
            );
        }

        $this->storageTypeToInitializerMap[$storageType->getValue()] = $storageTypeInitializer;
    }

    /**
     * @param StorageType $storageType
     *
     * @return bool
     */
    public function hasSupportFor(StorageType $storageType)
    {
        return isset($this->storageTypeToInitializerMap[$storageType->getValue()]);
    }

    /**
     * @param StorageType $storageType
     * @param Settings    $settings
     */
    public function applyFor(StorageType $storageType, Settings $settings)
    {
        $storageTypeInitializer = $this->storageTypeToInitializerMap[$storageType->getValue()];
        $storageTypeInitializer($settings);
    }

    /**
     * @param StorageType|null $storageType
     *
     * @return array
     */
    public function getSupportedStorages(StorageType $storageType = null)
    {
        if ($storageType) {
            return [$storageType->getValue()];
        }

        return array_keys($this->storageTypeToInitializerMap);
    }
}
