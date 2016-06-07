<?php
namespace acceptance\PSB\Core\Routing;


use acceptance\PSB\Core\Routing\WhenMultiSubscribingToAPolymorphicEventTest\MyEvent1;
use acceptance\PSB\Core\Routing\WhenMultiSubscribingToAPolymorphicEventTest\MyEvent2;
use acceptance\PSB\Core\Routing\WhenMultiSubscribingToAPolymorphicEventTest\Publishing1Endpoint;
use acceptance\PSB\Core\Routing\WhenMultiSubscribingToAPolymorphicEventTest\Publishing2Endpoint;
use acceptance\PSB\Core\Routing\WhenMultiSubscribingToAPolymorphicEventTest\RoutingContext;
use acceptance\PSB\Core\Routing\WhenMultiSubscribingToAPolymorphicEventTest\SubscriberEndpoint;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given Publishing1Endpoint, Publishing2Endpoint and SubscriberEndpoint
 * And messages MyEvent1 and MyEvent2 that implement MyEventInterface
 * And SubscriberEndpoint is subscribed to MyEventInterface
 *
 * When Publishing1Endpoint publishes MyEvent1
 * And Publishing2Endpoint publishes MyEvent2
 *
 * Then SubscriberEndpoint should receive both events
 */
class WhenMultiSubscribingToAPolymorphicEventTest extends ScenarioTestCase
{
    public function testBothEventsShouldBeReceived()
    {
        $result = $this->scenario
            ->givenContext(RoutingContext::class)
            ->givenEndpoint(
                new Publishing1Endpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $context->waitForGoFrom(SubscriberEndpoint::class);
                    $busContext->publish(new MyEvent1());
                }
            )
            ->givenEndpoint(
                new Publishing2Endpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $context->waitForGoFrom(SubscriberEndpoint::class);
                    $busContext->publish(new MyEvent2());
                }
            )
            ->givenEndpoint(
                new SubscriberEndpoint(),
                function (RunContext $context) {
                    $context->go();
                }
            )
            ->run();

        /** @var RoutingContext $context */
        $context = $result->getScenarioContext();
        $this->assertTrue($context->subscriberGotEvent1, "Subscriber should have received event 1.");
        $this->assertTrue($context->subscriberGotEvent2, "Subscriber should have received event 2.");
        $this->assertSame(
            0,
            $result->getMessageCountInQueueOf(SubscriberEndpoint::class),
            "Subscriber should have received only two events, not more."
        );
    }
}

namespace acceptance\PSB\Core\Routing\WhenMultiSubscribingToAPolymorphicEventTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;

class RoutingContext extends ScenarioContext
{
    public $subscriberGotEvent1;
    public $subscriberGotEvent2;
}

class Publishing1Endpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->enableSendOnly();
    }
}

class Publishing2Endpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->enableSendOnly();
    }
}

class SubscriberEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container([SubscriberHandler::class => new SubscriberHandler($this->scenarioContext)])
        );

        $this->registerEventHandler(MyEventInterface::class, SubscriberHandler::class);
    }
}

class SubscriberHandler implements MessageHandlerInterface
{
    private static $events = 0;

    /** @var RoutingContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        if ($message instanceof MyEvent1) {
            $this->scenarioContext->subscriberGotEvent1 = true;
        }
        if ($message instanceof MyEvent2) {
            $this->scenarioContext->subscriberGotEvent2 = true;
        }
        self::$events++;
        if (self::$events == 2) {
            $context->shutdownThisEndpointAfterCurrentMessage();
        }
    }
}

class MyEvent1 implements MyEventInterface
{
}

class MyEvent2 implements MyEventInterface
{
}

interface MyEventInterface
{
}
