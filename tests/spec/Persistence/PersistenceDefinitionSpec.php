<?php

namespace spec\PSB\Core\Persistence;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Util\Settings;
use spec\PSB\Core\Persistence\PersistenceDefinitionSpec\TestDefinition;
use specsupport\PSB\Core\ParametrizedCallable;

/**
 * @mixin TestDefinition
 */
class PersistenceDefinitionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beAnInstanceOf('spec\PSB\Core\Persistence\PersistenceDefinitionSpec\TestDefinition');
    }

    function it_has_support_for_outbox_after_formalizing(ParametrizedCallable $callable)
    {
        $this->setCallable($callable);
        $this->formalize();

        $this->hasSupportFor(StorageType::OUTBOX())->shouldReturn(true);
    }

    function it_has_support_for_outbox_before_formalizing(ParametrizedCallable $callable)
    {
        $this->setCallable($callable);
        $this->hasSupportFor(StorageType::OUTBOX())->shouldReturn(false);

        $this->formalize();
    }

    function it_invokes_callable_when_applying(ParametrizedCallable $callable, Settings $settings)
    {
        $this->setCallable($callable);
        $this->formalize();

        $callable->__invoke($settings)->shouldBeCalled();

        $this->applyFor(StorageType::OUTBOX(), $settings);
    }

    function it_returns_argument_storage_type_when_getting_supported_storages()
    {
        $this->getSupportedStorages(StorageType::OUTBOX())->shouldReturn(['Outbox']);
    }

    function it_returns_actually_supported_storages_when_getting_supported_storages_and_argument_is_null(
        ParametrizedCallable $callable
    ) {
        $this->setCallable($callable);
        $this->formalize();

        $this->getSupportedStorages()->shouldReturn(['Outbox']);
    }

    function it_throws_when_attempting_to_support_the_same_storage_types_multiple_times()
    {
        $this->beAnInstanceOf('spec\PSB\Core\Persistence\PersistenceDefinitionSpec\MalformedTestDefinition');

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringFormalize();
    }
}

namespace spec\PSB\Core\Persistence\PersistenceDefinitionSpec;

use PSB\Core\Persistence\PersistenceDefinition;
use PSB\Core\Persistence\StorageType;
use PSB\Core\Util\Settings;

class TestDefinition extends PersistenceDefinition
{
    private $callable;

    public function createConfigurator(Settings $settings)
    {
    }

    public function formalize()
    {
        $this->supports(StorageType::OUTBOX(), $this->callable);
    }

    public function setCallable(callable $callable)
    {
        $this->callable = $callable;
    }
}

class MalformedTestDefinition extends PersistenceDefinition
{
    public function createConfigurator(Settings $settings)
    {
    }

    public function formalize()
    {
        $this->supports(
            StorageType::OUTBOX(),
            function () {
            }
        );

        $this->supports(
            StorageType::OUTBOX(),
            function () {
            }
        );
    }
}
