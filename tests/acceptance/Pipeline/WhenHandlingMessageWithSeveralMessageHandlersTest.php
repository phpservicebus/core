<?php
namespace acceptance\PSB\Core\Pipeline;


use acceptance\PSB\Core\Pipeline\WhenHandlingMessageWithSeveralMessageHandlersTest\MyMessage;
use acceptance\PSB\Core\Pipeline\WhenHandlingMessageWithSeveralMessageHandlersTest\NormalEndpoint;
use acceptance\PSB\Core\Pipeline\WhenHandlingMessageWithSeveralMessageHandlersTest\PipelineContext;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given NormalEndpoint
 * And message MyMessage
 * And handlers FirstMessageHandler and SecondMessageHandler both handling MyMessage
 *
 * When NormalEndpoint sends MyMessage locally
 *
 * Then both handlers should be invoked
 */
class WhenHandlingMessageWithSeveralMessageHandlersTest extends ScenarioTestCase
{
    public function testShouldNotRetryAndMoveToErrorQueue()
    {
        $result = $this->scenario
            ->givenContext(PipelineContext::class)
            ->givenEndpoint(
                new NormalEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $busContext->sendLocal(new MyMessage());
                }
            )
            ->run();

        /** @var PipelineContext $context */
        $context = $result->getScenarioContext();
        $this->assertTrue($context->firstHandlerInvoked, "Message should be handlers by the first handler.");
        $this->assertTrue($context->secondHandlerInvoked, "Message should be handlerd by the second handler.");
    }
}

namespace acceptance\PSB\Core\Pipeline\WhenHandlingMessageWithSeveralMessageHandlersTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;

class PipelineContext extends ScenarioContext
{
    public $firstHandlerInvoked;
    public $secondHandlerInvoked;
}

class NormalEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container(
                [
                    FirstMessageHandler::class => new FirstMessageHandler($this->scenarioContext),
                    SecondMessageHandler::class => new SecondMessageHandler($this->scenarioContext)
                ]
            )
        );
        $this->registerCommandHandler(MyMessage::class, FirstMessageHandler::class);
        $this->registerCommandHandler(MyMessage::class, SecondMessageHandler::class);
    }
}

class FirstMessageHandler implements MessageHandlerInterface
{
    /** @var PipelineContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->firstHandlerInvoked = true;

        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class SecondMessageHandler implements MessageHandlerInterface
{
    /** @var PipelineContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->secondHandlerInvoked = true;
    }
}

class MyMessage
{
}
