<?php
namespace acceptance\PSB\Core\MessageMutation;


use acceptance\PSB\Core\MessageMutation\WhenRegisteringOutgoingMessageMutatorsTest\MutatingEndpoint;
use acceptance\PSB\Core\MessageMutation\WhenRegisteringOutgoingMessageMutatorsTest\MutationContext;
use acceptance\PSB\Core\MessageMutation\WhenRegisteringOutgoingMessageMutatorsTest\MyMessage;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given MutatingEndpoint
 * And message MyMessage
 * And outgoing mutators LogicalMutator and PhysicalMutator are registered
 *
 * When MutatingEndpoint sends MyMessage locally
 *
 * Then both mutators should be called
 */
class WhenRegisteringOutgoingMessageMutatorsTest extends ScenarioTestCase
{
    public function testShouldCallAllRegisteredMutators()
    {
        $result = $this->scenario
            ->givenContext(MutationContext::class)
            ->givenEndpoint(
                new MutatingEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $busContext->sendLocal(new MyMessage());
                }
            )
            ->run();

        /** @var MutationContext $context */
        $context = $result->getScenarioContext();
        $this->assertTrue($context->logicalMutatorInvoked, "Logical mutator should have been called.");
        $this->assertTrue($context->physicalMutatorInvoked, "Physical mutator should have been called.");
    }
}

namespace acceptance\PSB\Core\MessageMutation\WhenRegisteringOutgoingMessageMutatorsTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutationContext;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutatorInterface;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingPhysicalMessageMutationContext;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingPhysicalMessageMutatorInterface;
use PSB\Core\ObjectBuilder\Container;

class MutationContext extends ScenarioContext
{
    public $physicalMutatorInvoked;
    public $logicalMutatorInvoked;
}

class MutatingEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container(
                [
                    MessageHandler::class => new MessageHandler(),
                    LogicalMutator::class => new LogicalMutator($this->scenarioContext),
                    PhysicalMutator::class => new PhysicalMutator($this->scenarioContext)
                ]
            )
        );
        $this->registerCommandHandler(MyMessage::class, MessageHandler::class);
        $this->registerOutgoingLogicalMessageMutator(LogicalMutator::class);
        $this->registerOutgoingPhysicalMessageMutator(PhysicalMutator::class);
    }
}

class MessageHandler implements MessageHandlerInterface
{
    public function handle($message, MessageHandlerContextInterface $context)
    {
        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class PhysicalMutator implements OutgoingPhysicalMessageMutatorInterface
{
    /** @var MutationContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function mutateOutgoing(OutgoingPhysicalMessageMutationContext $context)
    {
        $this->scenarioContext->physicalMutatorInvoked = true;
    }
}

class LogicalMutator implements OutgoingLogicalMessageMutatorInterface
{
    /** @var MutationContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function mutateOutgoing(OutgoingLogicalMessageMutationContext $context)
    {
        $this->scenarioContext->logicalMutatorInvoked = true;
    }
}

class MyMessage
{
}
