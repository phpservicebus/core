<?php
namespace acceptance\PSB\Core\Outbox;


use acceptance\PSB\Core\Outbox\WhenRequestingImmediateDispatchTest\InitiatingMessage;
use acceptance\PSB\Core\Outbox\WhenRequestingImmediateDispatchTest\OutboxContext;
use acceptance\PSB\Core\Outbox\WhenRequestingImmediateDispatchTest\OutboxEndpoint;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given OutboxEndpoint
 * And messages InitiatingMessage and MessageToBeDispatchedImmediately
 * And handlers InitiatingMessageHandler and MessageToBeDispatchedImmediatelyHandler
 * And FLR disabled
 *
 * When InitiatingMessage is sent locally
 * And InitiatingMessageHandler blows up after sending MessageToBeDispatchedImmediately with immediate dispatch locally
 *
 * Then MessageToBeDispatchedImmediatelyHandler should be invoked
 */
class WhenRequestingImmediateDispatchTest extends ScenarioTestCase
{
    public function testShouldDispatchImmediately()
    {
        $result = $this->scenario
            ->givenContext(OutboxContext::class)
            ->givenEndpoint(
                new OutboxEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $busContext->sendLocal(new InitiatingMessage());
                }
            )
            ->run();

        /** @var OutboxContext $context */
        $context = $result->getScenarioContext();
        $this->assertTrue($context->messageIsDispatched, "Message should have been dispatched.");
    }
}

namespace acceptance\PSB\Core\Outbox\WhenRequestingImmediateDispatchTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\ErrorHandling\FirstLevelRetry\FirstLevelRetryFeature;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;
use PSB\Core\SendOptions;

class OutboxContext extends ScenarioContext
{
    public $messageIsDispatched;
}

class OutboxEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container(
                [
                    InitiatingMessageHandler::class => new InitiatingMessageHandler(),
                    MessageToBeDispatchedImmediatelyHandler::class => new MessageToBeDispatchedImmediatelyHandler(
                        $this->scenarioContext
                    )
                ]
            )
        );
        $this->registerCommandHandler(InitiatingMessage::class, InitiatingMessageHandler::class);
        $this->registerCommandHandler(
            MessageToBeDispatchedImmediately::class,
            MessageToBeDispatchedImmediatelyHandler::class
        );
        $this->registerCommandRoutingRule(InitiatingMessage::class, self::class);
        $this->registerCommandRoutingRule(MessageToBeDispatchedImmediately::class, self::class);
        $this->disableFeature(FirstLevelRetryFeature::class);
    }
}

class InitiatingMessage
{
}

class InitiatingMessageHandler implements MessageHandlerInterface
{
    public function handle($message, MessageHandlerContextInterface $context)
    {
        $options = new SendOptions();

        $options->requireImmediateDispatch();
        $context->sendLocal(new MessageToBeDispatchedImmediately(), $options);

        throw new \Exception('simulated');
    }
}

class MessageToBeDispatchedImmediately
{
}

class MessageToBeDispatchedImmediatelyHandler implements MessageHandlerInterface
{
    /** @var OutboxContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->messageIsDispatched = true;
        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}
