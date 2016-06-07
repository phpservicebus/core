<?php
namespace acceptance\PSB\Core\Outbox;


use acceptance\PSB\Core\Outbox\WhenReceivingMessagesAlreadyDispatchedTest\OutboxContext;
use acceptance\PSB\Core\Outbox\WhenReceivingMessagesAlreadyDispatchedTest\OutboxEndpoint;
use acceptance\PSB\Core\Outbox\WhenReceivingMessagesAlreadyDispatchedTest\PlaceOrder;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;
use PSB\Core\SendOptions;
use PSB\Core\UuidGeneration\Comb\TimestampFirstCombGenerator;

/**
 * Given OutboxEndpoint
 * And messages PlaceOrder and OrderAckReceived
 * And handlers PlaceOrderHandler and OrderAckReceivedHandler
 * And PlaceOrderHandler sends OrderAckReceived
 *
 * When three PlaceOrder messages are sent locally, two of which are dupes
 *
 * Then the second PlaceOrder dupe should not trigger dispatch of messages dispatched by first PlaceOrder dupe
 * And OrderAckReceivedHandler should be invoked exactly twice
 */
class WhenADuplicateMessageArrivesTest extends ScenarioTestCase
{
    public function testShouldNotDispatchMessagesAlreadyDispatched()
    {
        $result = $this->scenario
            ->givenContext(OutboxContext::class)
            ->givenEndpoint(
                new OutboxEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $uuidGenerator = new TimestampFirstCombGenerator();
                    $duplicateMessageId = $uuidGenerator->generate();

                    $options = new SendOptions();
                    $options->setMessageId($duplicateMessageId);
                    $options->routeToLocalEndpointInstance();

                    $busContext->send(new PlaceOrder(), $options);
                    $busContext->send(new PlaceOrder(), $options);
                    $busContext->sendLocal(new PlaceOrder());
                }
            )
            ->run();

        /** @var OutboxContext $context */
        $context = $result->getScenarioContext();
        $this->assertSame(2, $context->orderAckReceived, "Should ack at least the number of received unique messages.");
        $this->assertSame(
            0,
            $result->getMessageCountInQueueOf(OutboxEndpoint::class),
            "Should ack at most the number of received unique messages."
        );
    }
}

namespace acceptance\PSB\Core\Outbox\WhenReceivingMessagesAlreadyDispatchedTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;

class OutboxContext extends ScenarioContext
{
    public $orderAckReceived = 0;
}

class OutboxEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container(
                [
                    PlaceOrderHandler::class => new PlaceOrderHandler(),
                    SendOrderAcknowledgementHandler::class => new SendOrderAcknowledgementHandler(
                        $this->scenarioContext
                    )
                ]
            )
        );
        $this->registerCommandHandler(PlaceOrder::class, PlaceOrderHandler::class);
        $this->registerCommandHandler(SendOrderAcknowledgement::class, SendOrderAcknowledgementHandler::class);
        $this->registerCommandRoutingRule(PlaceOrder::class, self::class);
        $this->registerCommandRoutingRule(SendOrderAcknowledgement::class, self::class);
    }
}

class PlaceOrder
{
}

class PlaceOrderHandler implements MessageHandlerInterface
{
    public function handle($message, MessageHandlerContextInterface $context)
    {
        $context->sendLocal(new SendOrderAcknowledgement());
    }
}

class SendOrderAcknowledgement
{
}

class SendOrderAcknowledgementHandler implements MessageHandlerInterface
{
    /** @var OutboxContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->orderAckReceived++;

        if ($this->scenarioContext->orderAckReceived == 2) {
            $context->shutdownThisEndpointAfterCurrentMessage();
        }
    }
}
