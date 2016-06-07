<?php
namespace acceptance\PSB\Core\Routing;


use acceptance\PSB\Core\Routing\WhenSendingFromSendOnlyTest\RoutingContext;
use acceptance\PSB\Core\Routing\WhenSendingFromSendOnlyTest\MyCommand;
use acceptance\PSB\Core\Routing\WhenSendingFromSendOnlyTest\SendingEndpoint;
use acceptance\PSB\Core\Routing\WhenSendingFromSendOnlyTest\ReceivingEndpoint;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given SendingEndpoint and ReceivingEndpoint
 * And a command MyCommand
 * And SendingEndpoint is send only
 *
 * When SendingEndpoint sends MyCommand to ReceivingEndpoint
 *
 * Then ReceivingEndpoint should receive it
 */
class WhenSendingFromSendOnlyTest extends ScenarioTestCase
{
    public function testShouldReceiveTheMessage()
    {
        $result = $this->scenario
            ->givenContext(RoutingContext::class)
            ->givenEndpoint(
                new SendingEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $context->waitForGoFrom(ReceivingEndpoint::class);
                    $busContext->send(new MyCommand());
                }
            )
            ->givenEndpoint(
                new ReceivingEndpoint(),
                function (RunContext $context) {
                    $context->go();
                }
            )
            ->run();

        /** @var RoutingContext $context */
        $context = $result->getScenarioContext();
        $this->assertTrue($context->receiverGotTheMessage, "Receiver should have received the message.");
    }
}

namespace acceptance\PSB\Core\Routing\WhenSendingFromSendOnlyTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;

class RoutingContext extends ScenarioContext
{
    public $receiverGotTheMessage;
}

class SendingEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->enableSendOnly();
        $this->registerCommandRoutingRule(MyCommand::class, ReceivingEndpoint::class);
    }
}

class ReceivingEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(new Container([ReceivingHandler::class => new ReceivingHandler($this->scenarioContext)]));
        $this->registerEventHandler(MyCommand::class, ReceivingHandler::class);
    }
}

class ReceivingHandler implements MessageHandlerInterface
{
    /** @var RoutingContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->receiverGotTheMessage = true;
        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class MyCommand
{
}
