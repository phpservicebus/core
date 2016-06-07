<?php
namespace PSB\Core\Persistence\InMemory;


use PSB\Core\Feature\FeatureSettingsExtensions;
use PSB\Core\Persistence\InMemory\Outbox\InMemoryOutboxPersistenceFeature;
use PSB\Core\Persistence\PersistenceDefinition;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Util\Settings;

class InMemoryPersistenceDefinition extends PersistenceDefinition
{
    /**
     * Creates the PersistenceConfigurator specific to the transport implementation.
     *
     * @param Settings $settings
     *
     * @return InMemoryPersistenceConfigurator
     */
    public function createConfigurator(Settings $settings)
    {
        return new InMemoryPersistenceConfigurator($settings);
    }

    /**
     * This is where subclasses declare what storage types they support together with
     * the initializers for those types.
     */
    public function formalize()
    {
        $this->supports(
            StorageType::OUTBOX(),
            function (Settings $s) {
                FeatureSettingsExtensions::enableFeatureByDefault(
                    InMemoryOutboxPersistenceFeature::class,
                    $s
                );
            }
        );
    }
}
