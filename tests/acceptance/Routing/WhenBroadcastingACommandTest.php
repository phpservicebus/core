<?php
namespace acceptance\PSB\Core\Routing;


use acceptance\PSB\Core\Routing\WhenBroadcastingACommand\MyCommand;
use acceptance\PSB\Core\Routing\WhenBroadcastingACommand\Receiving1Endpoint;
use acceptance\PSB\Core\Routing\WhenBroadcastingACommand\Receiving2Endpoint;
use acceptance\PSB\Core\Routing\WhenBroadcastingACommand\RoutingContext;
use acceptance\PSB\Core\Routing\WhenBroadcastingACommand\SendingEndpoint;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given SendingEndpoint, Receiving1Endpoint and Receiving2Endpoint
 * And message MyCommand
 * And SendingEndpoint is configured to route MyCommand to Receiving1Endpoint
 * And SendingEndpoint is configured to route MyCommand to Receiving2Endpoint
 *
 * When SendingEndpoint sends MyCommand
 *
 * Then Receiving1Endpoint and Receiving2Endpoint should both receive MyCommand
 */
class WhenBroadcastingACommandTest extends ScenarioTestCase
{
    public function testShouldSendItToAllTargetedEndpoints()
    {
        $result = $this->scenario
            ->givenContext(RoutingContext::class)
            ->givenEndpoint(
                new SendingEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $context->waitForGoFrom(Receiving1Endpoint::class);
                    $context->waitForGoFrom(Receiving2Endpoint::class);
                    $busContext->send(new MyCommand());
                }
            )
            ->givenEndpoint(
                new Receiving1Endpoint(),
                function (RunContext $context) {
                    $context->go();
                }
            )
            ->givenEndpoint(
                new Receiving2Endpoint(),
                function (RunContext $context) {
                    $context->go();
                }
            )
            ->run();

        /** @var RoutingContext $context */
        $context = $result->getScenarioContext();
        $this->assertTrue($context->receiving1EndpointGotCommand, "Receiver 1 should have received the command.");
        $this->assertTrue($context->receiving2EndpointGotCommand, "Receiver 2 should have received the command.");
    }
}

namespace acceptance\PSB\Core\Routing\WhenBroadcastingACommand;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;

class RoutingContext extends ScenarioContext
{
    public $receiving1EndpointGotCommand;
    public $receiving2EndpointGotCommand;
}

class SendingEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->enableSendOnly();
        $this->registerCommandRoutingRule(MyCommand::class, Receiving1Endpoint::class);
        $this->registerCommandRoutingRule(MyCommand::class, Receiving2Endpoint::class);
    }
}

class Receiving1Endpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(new Container([Command1Handler::class => new Command1Handler($this->scenarioContext)]));
        $this->registerCommandHandler(MyCommand::class, Command1Handler::class);
    }
}

class Command1Handler implements MessageHandlerInterface
{
    /** @var RoutingContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->receiving1EndpointGotCommand = true;

        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class Receiving2Endpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(new Container([Command2Handler::class => new Command2Handler($this->scenarioContext)]));
        $this->registerCommandHandler(MyCommand::class, Command2Handler::class);
    }
}

class Command2Handler implements MessageHandlerInterface
{
    /** @var RoutingContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->receiving2EndpointGotCommand = true;

        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class MyCommand
{
}
