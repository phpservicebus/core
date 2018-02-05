<?php

namespace spec\PSB\Core\Persistence\InMemory;

use PhpSpec\ObjectBehavior;

use PSB\Core\Persistence\InMemory\InMemoryPersistenceConfigurator;
use PSB\Core\Persistence\InMemory\InMemoryPersistenceDefinition;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Util\Settings;

/**
 * @mixin InMemoryPersistenceDefinition
 */
class InMemoryPersistenceDefinitionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Persistence\InMemory\InMemoryPersistenceDefinition');
    }

    function it_creates_a_configurator(Settings $settings)
    {
        $this->createConfigurator($settings)->shouldBeLike(
            new InMemoryPersistenceConfigurator($settings->getWrappedObject())
        );
    }

    function it_formalizes_by_declaring_support_for_outbox()
    {
        $this->formalize();
        $this->hasSupportFor(StorageType::OUTBOX())->shouldReturn(true);
    }
}
