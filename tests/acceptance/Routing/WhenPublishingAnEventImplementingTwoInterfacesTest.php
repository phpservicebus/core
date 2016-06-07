<?php
namespace acceptance\PSB\Core\Routing;


use acceptance\PSB\Core\Routing\WhenPublishingAnEventImplementingTwoInterfacesTest\MyEvent;
use acceptance\PSB\Core\Routing\WhenPublishingAnEventImplementingTwoInterfacesTest\PublishingEndpoint;
use acceptance\PSB\Core\Routing\WhenPublishingAnEventImplementingTwoInterfacesTest\RoutingContext;
use acceptance\PSB\Core\Routing\WhenPublishingAnEventImplementingTwoInterfacesTest\Subscriber1Endpoint;
use acceptance\PSB\Core\Routing\WhenPublishingAnEventImplementingTwoInterfacesTest\Subscriber2Endpoint;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given PublishingEndpoint, Subscriber1Endpoint and Subscriber2Endpoint
 * And a message MyEvent that implements MyEvent1Interface and MyEvent2Interface
 * And Subscriber1Endpoint is subscribed to MyEvent1Interface
 * And Subscriber2Endpoint is subscribed to MyEvent2Interface
 *
 * When PublishingEndpoint publishes MyEvent
 *
 * Then both subscribers should receive it
 */
class WhenPublishingAnEventImplementingTwoInterfacesTest extends ScenarioTestCase
{
    public function testShouldBeDeliveredToAllInterfaceSubscribers()
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
        $this->assertTrue($context->subscriber1GotTheEvent, "Subscriber 1 should have received the event.");
        $this->assertTrue($context->subscriber2GotTheEvent, "Subscriber 2 should have received the event.");
    }
}

namespace acceptance\PSB\Core\Routing\WhenPublishingAnEventImplementingTwoInterfacesTest;

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

        $this->registerEventHandler(MyEvent1Interface::class, Subscriber1Handler::class);
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

        $this->registerEventHandler(MyEvent2Interface::class, Subscriber2Handler::class);
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


class MyEvent implements MyEvent1Interface, MyEvent2Interface
{
}

interface MyEvent1Interface
{
}

interface MyEvent2Interface
{
}
