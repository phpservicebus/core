<?php
namespace PSB\Core\Persistence;


class EnabledPersistence
{
    /**
     * @var PersistenceDefinition
     */
    private $definition;

    /**
     * @var StorageType
     */
    private $selectedStorageType;

    /**
     * @param PersistenceDefinition $definition
     * @param StorageType|null      $selectedStorageType
     */
    public function __construct(PersistenceDefinition $definition, StorageType $selectedStorageType = null)
    {
        $this->definition = $definition;
        $this->selectedStorageType = $selectedStorageType;
    }

    /**
     * @return PersistenceDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @return StorageType|null
     */
    public function getSelectedStorageType()
    {
        return $this->selectedStorageType;
    }
}
