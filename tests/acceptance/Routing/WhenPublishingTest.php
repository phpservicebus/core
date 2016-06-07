<?php
namespace acceptance\PSB\Core\Routing;


use acceptance\PSB\Core\Routing\WhenPublishingTest\RoutingContext;
use acceptance\PSB\Core\Routing\WhenPublishingTest\PublishingEndpoint;
use acceptance\PSB\Core\Routing\WhenPublishingTest\Subscriber1Endpoint;
use acceptance\PSB\Core\Routing\WhenPublishingTest\Subscriber2Endpoint;
use acceptance\PSB\Core\Routing\WhenPublishingTest\TriggeringEndpoint;
use acceptance\PSB\Core\Routing\WhenPublishingTest\TriggerPublisher;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given TriggeringEndpoint, PublishingEndpoint, Subscriber1Endpoint and Subscriber2Endpoint
 * And messages MyEvent and TriggerPublisher
 * And Subscriber1Endpoint and Subscriber2Endpoint are subscribed to MyEvent
 * And PublishingEndpoint is not send only
 *
 * When TriggeringEndpoint sends TriggerPublisher
 * And PublishingEndpoint publishes MyEvent
 *
 * Then both subscribers should receive it
 */
class WhenPublishingTest extends ScenarioTestCase
{
    public function testShouldBeDeliveredToAllSubscribers()
    {
        $result = $this->scenario
            ->givenContext(RoutingContext::class)
            ->givenEndpoint(
                new TriggeringEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $context->waitForGoFrom(PublishingEndpoint::class);
                    $context->waitForGoFrom(Subscriber1Endpoint::class);
                    $context->waitForGoFrom(Subscriber2Endpoint::class);
                    $busContext->send(new TriggerPublisher());
                }
            )
            ->givenEndpoint(
                new PublishingEndpoint(),
                function (RunContext $context) {
                    $context->go();
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

namespace acceptance\PSB\Core\Routing\WhenPublishingTest;

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

class TriggeringEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->enableSendOnly();
        $this->registerCommandRoutingRule(TriggerPublisher::class, PublishingEndpoint::class);
    }
}

class PublishingEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(new Container([PublishingHandler::class => new PublishingHandler()]));
        $this->registerCommandHandler(TriggerPublisher::class, PublishingHandler::class);
    }
}

class PublishingHandler implements MessageHandlerInterface
{
    public function handle($message, MessageHandlerContextInterface $context)
    {
        $context->publish(new MyEvent());

        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class Subscriber1Endpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $handler = new Subscriber1Handler();
        $handler->scenarioContext = $this->scenarioContext;

        $this->useContainer(new Container([Subscriber1Handler::class => $handler]));

        $this->registerEventHandler(MyEvent::class, Subscriber1Handler::class);
    }
}

class Subscriber1Handler implements MessageHandlerInterface
{
    /** @var RoutingContext */
    public $scenarioContext;

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
        $handler = new Subscriber2Handler();
        $handler->scenarioContext = $this->scenarioContext;

        $this->useContainer(new Container([Subscriber2Handler::class => $handler]));

        $this->registerEventHandler(MyEvent::class, Subscriber2Handler::class);
    }
}

class Subscriber2Handler implements MessageHandlerInterface
{
    /** @var RoutingContext */
    public $scenarioContext;

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->subscriber2GotTheEvent = true;
        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}


class MyEvent
{
}

class TriggerPublisher
{
}
