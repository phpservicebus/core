<?php
namespace acceptance\PSB\Core\Outbox;


use acceptance\PSB\Core\Outbox\WhenBlowingUpJustAfterDispatchTest\OutboxContext;
use acceptance\PSB\Core\Outbox\WhenBlowingUpJustAfterDispatchTest\OutboxEndpoint;
use acceptance\PSB\Core\Outbox\WhenBlowingUpJustAfterDispatchTest\PlaceOrder;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given OutboxEndpoint
 * And messages PlaceOrder and OrderAckReceived
 * And handlers PlaceOrderHandler and OrderAckReceivedHandler
 * And PlaceOrderHandler will send OrderAckReceived
 *
 * When PlaceOrder is sent locally
 * And PlaceOrderHandler sends OrderAckReceived locally
 * And the universe blows up right after dispatching OrderAckReceived
 *
 * Then OrderAckReceivedHandler should be invoked
 */
class WhenBlowingUpJustAfterDispatchTest extends ScenarioTestCase
{
    public function testShouldStillReleaseTheOutgoingMessagesToTheTransport()
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
            1,
            $context->orderAckReceived,
            "Order ack should have been received since outbox dispatch isn't part of the receive tx"
        );
    }
}

namespace acceptance\PSB\Core\Outbox\WhenBlowingUpJustAfterDispatchTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;
use PSB\Core\Pipeline\Outgoing\StageContext\DispatchContext;
use PSB\Core\Pipeline\PipelineStepInterface;

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

        $this->registerPipelineStep(
            'BlowUpBeforeDispatchPipelineStep',
            BlowUpBeforeDispatchPipelineStep::class,
            function () {
                return new BlowUpBeforeDispatchPipelineStep();
            }
        );
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

        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class BlowUpBeforeDispatchPipelineStep implements PipelineStepInterface
{
    private static $blownOnce = false;

    /**
     * @param DispatchContext $context
     * @param callable        $next
     *
     * @throws \Exception
     */
    public function invoke($context, callable $next)
    {
        if ($this->isForPlaceOrder($context)) {
            $next();
            return;
        }

        if (self::$blownOnce) {
            return;
        }

        $next();

        self::$blownOnce = true;
        throw new \Exception('simulated');
    }

    public static function getStageContextClass()
    {
        return DispatchContext::class;
    }

    /**
     * @param DispatchContext $context
     *
     * @return bool
     */
    private function isForPlaceOrder($context)
    {
        foreach ($context->getTransportOperations() as $transportOperation) {
            $headers = $transportOperation->getMessage()->getHeaders();
            if ($headers[HeaderTypeEnum::ENCLOSED_CLASS] == PlaceOrder::class) {
                return true;
            }
        }
        return false;
    }
}
