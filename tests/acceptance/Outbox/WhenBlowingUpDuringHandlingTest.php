<?php
namespace acceptance\PSB\Core\Outbox;


use acceptance\PSB\Core\Outbox\WhenBlowingUpDuringHandling\OutboxContext;
use acceptance\PSB\Core\Outbox\WhenBlowingUpDuringHandling\OutboxEndpoint;
use acceptance\PSB\Core\Outbox\WhenBlowingUpDuringHandling\PlaceOrder;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given OutboxEndpoint
 * And messages PlaceOrder and OrderAckReceived
 * And handlers PlaceOrderHandler and OrderAckReceivedHandler
 * And PlaceOrderHandler will send OrderAckReceived locally
 *
 * When PlaceOrder is sent locally
 * And PlaceOrderHandler blows up the first time after sending OrderAckReceived locally
 * And does not blow up the second time
 *
 * Then the initial outgoing OrderAckReceived should be discarded during transaction rollback
 * And PlaceOrderHandler should be retried once
 * And the second outgoing OrderAckReceived should be dispatched
 * And OrderAckReceivedHandler should be invoked exactly once
 */
class WhenBlowingUpDuringHandlingTest extends ScenarioTestCase
{
    public function testShouldDiscardTheOutgoingMessagesAndRetry()
    {
        $result = $this->scenario
            ->givenContext(OutboxContext::class)
            ->givenEndpoint(
                new OutboxEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $busContext->sendLocal(new PlaceOrder());
                }
            )
            ->run();

        /** @var OutboxContext $context */
        $context = $result->getScenarioContext();
        $this->assertSame(
            2,
            $context->placeOrderHandlerExecuted,
            "PlaceOrderHandler should have executed twice, first time by throwing an exception and the second time normally."
        );
        $this->assertSame(1, $context->orderAckReceived, "Order ack should have been received at least once.");
        $this->assertSame(
            0,
            $result->getMessageCountInQueueOf(OutboxEndpoint::class),
            "Order ack should have been received at most once."
        );
    }
}

namespace acceptance\PSB\Core\Outbox\WhenBlowingUpDuringHandling;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;

class OutboxContext extends ScenarioContext
{
    public $orderAckReceived = 0;
    public $placeOrderHandlerExecuted = 0;
}

class OutboxEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container(
                [
                    PlaceOrderHandler::class => new PlaceOrderHandler($this->scenarioContext),
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
    private static $blownOnce = false;

    /** @var OutboxContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->placeOrderHandlerExecuted++;
        $context->sendLocal(new SendOrderAcknowledgement());

        if (self::$blownOnce) {
            return;
        }

        self::$blownOnce = true;
        throw new \Error('simulated');
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
        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}
