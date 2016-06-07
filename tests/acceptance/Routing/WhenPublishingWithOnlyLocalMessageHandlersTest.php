<?php
namespace acceptance\PSB\Core\Routing;


use acceptance\PSB\Core\Routing\WhenPublishingWithOnlyLocalMessageHandlersTest\RoutingContext;
use acceptance\PSB\Core\Routing\WhenPublishingWithOnlyLocalMessageHandlersTest\EventHandledByLocalEndpoint;
use acceptance\PSB\Core\Routing\WhenPublishingWithOnlyLocalMessageHandlersTest\PublishingEndpoint;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given PublishingEndpoint
 * And a message EventHandledByLocalEndpoint
 * And PublishingEndpoint is subscribed to EventHandledByLocalEndpoint
 *
 * When PublishingEndpoint publishes EventHandledByLocalEndpoint
 *
 * Then PublishingEndpoint should receive it
 */
class WhenPublishingWithOnlyLocalMessageHandlersTest extends ScenarioTestCase
{
    public function testShouldBeDeliveredToAllSubscribers()
    {
        $result = $this->scenario
            ->givenContext(RoutingContext::class)
            ->givenEndpoint(
                new PublishingEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $busContext->publish(new EventHandledByLocalEndpoint());
                }
            )
            ->run();

        /** @var RoutingContext $context */
        $context = $result->getScenarioContext();
        $this->assertTrue($context->publisherGotTheEvent, "Publisher should have received it's event.");
    }
}

namespace acceptance\PSB\Core\Routing\WhenPublishingWithOnlyLocalMessageHandlersTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;

class RoutingContext extends ScenarioContext
{
    public $publisherGotTheEvent;
}

class PublishingEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container([PublisherHandler::class => new PublisherHandler($this->scenarioContext)])
        );

        $this->registerEventHandler(EventHandledByLocalEndpoint::class, PublisherHandler::class);
    }
}

class PublisherHandler implements MessageHandlerInterface
{
    /** @var RoutingContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->publisherGotTheEvent = true;
        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class EventHandledByLocalEndpoint
{
}
