<?php
namespace acceptance\PSB\Core\Correlation;


use acceptance\PSB\Core\Correlation\WhenSendingWithNoCorrelationIdTest\CorrelationContext;
use acceptance\PSB\Core\Correlation\WhenSendingWithNoCorrelationIdTest\CorrelationEndpoint;
use acceptance\PSB\Core\Correlation\WhenSendingWithNoCorrelationIdTest\MyMessage;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given CorrelationEndpoint
 * And message MyMessage
 * And MessageHandler that handles MyMessage
 *
 * When CorrelationEndpoint sends MyMessage locally
 *
 * Then the received message id should be the same as the received message correlation id
 */
class WhenSendingWithNoCorrelationIdTest extends ScenarioTestCase
{
    public function testShouldUseTheMessageIdAsTheCorrelationId()
    {
        $result = $this->scenario
            ->givenContext(CorrelationContext::class)
            ->givenEndpoint(
                new CorrelationEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $busContext->sendLocal(new MyMessage());
                }
            )
            ->run();

        /** @var CorrelationContext $context */
        $context = $result->getScenarioContext();
        $this->assertSame(
            $context->messageIdReceived,
            $context->correlationIdReceived,
            "Correlation id should be the message id."
        );
    }
}

namespace acceptance\PSB\Core\Correlation\WhenSendingWithNoCorrelationIdTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;

class CorrelationContext extends ScenarioContext
{
    public $messageIdReceived;
    public $correlationIdReceived;
}

class CorrelationEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(new Container([MessageHandler::class => new MessageHandler($this->scenarioContext)]));
        $this->registerCommandHandler(MyMessage::class, MessageHandler::class);
    }
}

class MessageHandler implements MessageHandlerInterface
{
    /** @var CorrelationContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->messageIdReceived = $context->getMessageId();
        $this->scenarioContext->correlationIdReceived = $context->getHeaders()[HeaderTypeEnum::CORRELATION_ID];
        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class MyMessage
{
}
