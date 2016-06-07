<?php
namespace acceptance\PSB\Core\Persistence;


use acceptance\PSB\Core\Persistence\WhenAPersistenceDoesNotSupportOutboxTest\NormalEndpoint;
use acceptance\PSB\Core\Persistence\WhenAPersistenceDoesNotSupportOutboxTest\PersistenceContext;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;

/**
 * Given NormalEndpoint
 * And persistence definition NoOutboxPersistenceDefinition
 * And NoOutboxPersistenceDefinition does not support outbox storage type
 *
 * When NormalEndpoint is started
 *
 * Then it should throw an exception suggesting disabling of the outbox feature
 */
class WhenAPersistenceDoesNotSupportOutboxTest extends ScenarioTestCase
{
    public function testShouldThrowException()
    {
        $result = $this->scenario
            ->givenContext(PersistenceContext::class)
            ->givenEndpoint(new NormalEndpoint())
            ->run();

        $this->assertContains(
            'disableFeature',
            $result->getErrorFor(NormalEndpoint::class)->getMessage(),
            "The exception message should have suggested disabling the outbox feature."
        );
    }
}

namespace acceptance\PSB\Core\Persistence\WhenAPersistenceDoesNotSupportOutboxTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\Persistence\PersistenceDefinition;
use PSB\Core\Util\Settings;

class PersistenceContext extends ScenarioContext
{
}

class NormalEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->clearPersistences();
        $this->usePersistence(new NoOutboxPersistenceDefinition());
    }
}

class NoOutboxPersistenceDefinition extends PersistenceDefinition
{
    public function createConfigurator(Settings $settings)
    {
    }

    public function formalize()
    {
    }
}

