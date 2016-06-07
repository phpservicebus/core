<?php
namespace acceptance\PSB\Core\Routing;


use acceptance\PSB\Core\Routing\WhenPublishingFromSendOnlyTest\MyEvent;
use acceptance\PSB\Core\Routing\WhenPublishingFromSendOnlyTest\PublishingEndpoint;
use acceptance\PSB\Core\Routing\WhenPublishingFromSendOnlyTest\RoutingContext;
use acceptance\PSB\Core\Routing\WhenPublishingFromSendOnlyTest\Subscriber1Endpoint;
use acceptance\PSB\Core\Routing\WhenPublishingFromSendOnlyTest\Subscriber2Endpoint;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given PublishingEndpoint, Subscriber1Endpoint and Subscriber2Endpoint
 * And a message MyEvent
 * And Subscriber1Endpoint and Subscriber2Endpoint are subscribed to MyEvent
 * And PublishingEndpoint is send only
 *
 * When PublishingEndpoint publishes MyEvent
 *
 * Then both subscribers should receive it
 */
class WhenPublishingFromSendOnlyTest extends ScenarioTestCase
{
    public function testShouldBeDeliveredToAllSubscribers()
    {
        $result = $this->scenario
            ->givenContext(RoutingContext::class)
            ->givenEndpoint(
                new PublishingEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $context->waitForGoFrom(Subscriber1Endpoint::class);
                    $context->waitForGoFrom(Subscriber2Endpoint::class);
                    $busContext->publish(new MyEvent());
                }
            )
            ->givenEndpoint(
                new Subscriber1Endpoint(),
                function (RunContext $context) {
                    $context->go();
                }
            )
            ->givenEndpoint(
                new Subscriber2Endpoint(),
                function (RunContext $context) {
                    $context->go();
                }
            )
            ->run();

        /** @var RoutingContext $context */
        $context = $result->getScenarioContext();
        $this->assertTrue($context->subscriber1GotTheEvent, "Subscriber 1 should have received it's event.");
        $this->assertTrue($context->subscriber2GotTheEvent, "Subscriber 2 should have received it's event.");
    }
}

namespace acceptance\PSB\Core\Routing\WhenPublishingFromSendOnlyTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;

class RoutingContext extends ScenarioContext
{
    public $subscriber1GotTheEvent;
    public $subscriber2GotTheEvent;
}

class PublishingEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->enableSendOnly();
    }
}

class Subscriber1Endpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container([Subscriber1Handler::class => new Subscriber1Handler($this->scenarioContext)])
        );
        $this->registerEventHandler(MyEvent::class, Subscriber1Handler::class);
    }
}

class Subscriber1Handler implements MessageHandlerInterface
{
    /** @var RoutingContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->subscriber1GotTheEvent = true;
        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class Subscriber2Endpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container([Subscriber2Handler::class => new Subscriber2Handler($this->scenarioContext)])
        );
        $this->registerEventHandler(MyEvent::class, Subscriber2Handler::class);
    }
}

class Subscriber2Handler implements MessageHandlerInterface
{
    /** @var RoutingContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->subscriber2GotTheEvent = true;
        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}


class MyEvent
{
}
