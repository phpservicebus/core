<?php
namespace acceptance\PSB\Core\MessageMutation;


use acceptance\PSB\Core\MessageMutation\WhenOutgoingPhysicalMutatorUpdatesMessageTypeTest\MutatingEndpoint;
use acceptance\PSB\Core\MessageMutation\WhenOutgoingPhysicalMutatorUpdatesMessageTypeTest\MutationContext;
use acceptance\PSB\Core\MessageMutation\WhenOutgoingPhysicalMutatorUpdatesMessageTypeTest\OriginalMessage;
use acceptance\PSB\Core\MessageMutation\WhenOutgoingPhysicalMutatorUpdatesMessageTypeTest\ReplacementMessage;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given MutatingEndpoint
 * And messages OriginalMessage and ReplacementMessage which have the same members
 * And outgoing physical mutator TypeReplacementMutator is registered
 * And TypeReplacementMutator replaces OriginalMessage with ReplacementMessage as type but not as content
 *
 * When MutatingEndpoint sends OriginalMessage locally
 *
 * Then ReplacementMessage with the original content should be received
 */
class WhenOutgoingPhysicalMutatorUpdatesMessageTypeTest extends ScenarioTestCase
{
    public function testUpdatedMessageShouldBeSent()
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
        $this->assertTrue(
            $context->replacementMessageReceived,
            "Replacement message handler should have been invoked."
        );
        $this->assertSame(
            5,
            $context->replacementMessageValue,
            "Replacement message should have the value of the original message."
        );
        $this->assertSame(
            ReplacementMessage::class,
            $context->receivedMessageType,
            "Received message should have the ReplacementMessage instance type."
        );
        $this->assertNull($context->originalMessageReceived, "Original message handler should not have been invoked.");
    }
}

namespace acceptance\PSB\Core\MessageMutation\WhenOutgoingPhysicalMutatorUpdatesMessageTypeTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingPhysicalMessageMutationContext;
use PSB\Core\MessageMutation\Pipeline\Outgoing\OutgoingPhysicalMessageMutatorInterface;
use PSB\Core\ObjectBuilder\Container;

class MutationContext extends ScenarioContext
{
    public $originalMessageReceived;
    public $replacementMessageReceived;
    public $replacementMessageValue;
    public $receivedMessageType;
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
                    TypeReplacementMutator::class => new TypeReplacementMutator(),
                ]
            )
        );
        $this->registerCommandHandler(OriginalMessage::class, OriginalMessageHandler::class);
        $this->registerCommandHandler(ReplacementMessage::class, ReplacementMessageHandler::class);
        $this->registerOutgoingPhysicalMessageMutator(TypeReplacementMutator::class);
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
        $this->scenarioContext->receivedMessageType = get_class($message);

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

    /**
     * @param ReplacementMessage             $message
     * @param MessageHandlerContextInterface $context
     */
    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->replacementMessageReceived = true;
        $this->scenarioContext->replacementMessageValue = $message->value;
        $this->scenarioContext->receivedMessageType = get_class($message);

        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class TypeReplacementMutator implements OutgoingPhysicalMessageMutatorInterface
{
    public function mutateOutgoing(OutgoingPhysicalMessageMutationContext $context)
    {
        $body = str_replace('OriginalMessage', 'ReplacementMessage', $context->getBody());
        $context->replaceBody($body);
        $context->setHeader(HeaderTypeEnum::ENCLOSED_CLASS, ReplacementMessage::class);
    }
}

class OriginalMessage
{
    public $value = 5;
}

class ReplacementMessage
{
    public $value = 3;
}
