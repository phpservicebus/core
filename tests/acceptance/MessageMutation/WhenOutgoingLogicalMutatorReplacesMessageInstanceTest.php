<?php
namespace acceptance\PSB\Core\MessageMutation;


use acceptance\PSB\Core\MessageMutation\WhenOutgoingLogicalMutatorReplacesMessageInstanceTest\MutatingEndpoint;
use acceptance\PSB\Core\MessageMutation\WhenOutgoingLogicalMutatorReplacesMessageInstanceTest\MutationContext;
use acceptance\PSB\Core\MessageMutation\WhenOutgoingLogicalMutatorReplacesMessageInstanceTest\OriginalMessage;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given MutatingEndpoint
 * And messages OriginalMessage and ReplacementMessage
 * And outgoing logical mutator InstanceReplacementMutator is registered
 * And InstanceReplacementMutator replaces OriginalMessage with ReplacementMessage
 *
 * When MutatingEndpoint sends OriginalMessage locally
 *
 * Then ReplacementMessage should be received
 */
class WhenOutgoingLogicalMutatorReplacesMessageInstanceTest extends ScenarioTestCase
{
    public function testMessageSentShouldBeNewInstance()
    {
        $result = $this->scenario
            ->givenContext(MutationContext::class)
            ->givenEndpoint(
                new MutatingEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $busContext->sendLocal(new OriginalMessage());
                }
            )
            ->run();

        /** @var MutationContext $context */
        $context = $result->getScenarioContext();
        $this->assertTrue($context->replacementMessageReceived, "Replacement message should have been received.");
        $this->assertNull($context->originalMessageReceived, "Original message snould not have been received.");
    }
}

namespace acceptance\PSB\Core\MessageMutation\WhenOutgoingLogicalMutatorReplacesMessageInstanceTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutationContext;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingLogicalMessageMutatorInterface;
use PSB\Core\ObjectBuilder\Container;

class MutationContext extends ScenarioContext
{
    public $originalMessageReceived;
    public $replacementMessageReceived;
}

class MutatingEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container(
                [
                    OriginalMessageHandler::class => new OriginalMessageHandler($this->scenarioContext),
                    ReplacementMessageHandler::class => new ReplacementMessageHandler($this->scenarioContext),
                    InstanceReplacementMutator::class => new InstanceReplacementMutator(),
                ]
            )
        );
        $this->registerCommandHandler(OriginalMessage::class, OriginalMessageHandler::class);
        $this->registerCommandHandler(ReplacementMessage::class, ReplacementMessageHandler::class);
        $this->registerOutgoingLogicalMessageMutator(InstanceReplacementMutator::class);
    }
}

class OriginalMessageHandler implements MessageHandlerInterface
{
    /** @var MutationContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->originalMessageReceived = true;

        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class ReplacementMessageHandler implements MessageHandlerInterface
{
    /** @var MutationContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->replacementMessageReceived = true;

        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class InstanceReplacementMutator implements OutgoingLogicalMessageMutatorInterface
{
    public function mutateOutgoing(OutgoingLogicalMessageMutationContext $context)
    {
        $context->updateMessage(new ReplacementMessage());
    }
}

class OriginalMessage
{
}

class ReplacementMessage
{
}
