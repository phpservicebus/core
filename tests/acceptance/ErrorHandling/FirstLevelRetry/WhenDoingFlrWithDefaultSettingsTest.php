<?php
namespace acceptance\PSB\Core\ErrorHandling\FirstLevelRetry;


use acceptance\PSB\Core\ErrorHandling\FirstLevelRetry\WhenDoingFlrWithDefaultSettingsTest\FLRContext;
use acceptance\PSB\Core\ErrorHandling\FirstLevelRetry\WhenDoingFlrWithDefaultSettingsTest\FLREndpoint;
use acceptance\PSB\Core\ErrorHandling\FirstLevelRetry\WhenDoingFlrWithDefaultSettingsTest\MyMessage;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

class WhenDoingFlrWithDefaultSettingsTest extends ScenarioTestCase
{
    /**
     * Given FLREndpoint
     * And message MyMessage
     * And handler MyMessageHandler
     *
     * When FLREndpoint sends MyMessage locally
     * And MyMessageHandler continuously blows up
     *
     * Then MyMessage should be retried 5 times
     * And MyMessage should be sent to the error queue
     */
    public function testShouldDo5RetriesAndMoveToErrorQueue()
    {
        $result = $this->scenario
            ->givenContext(FLRContext::class)
            ->givenEndpoint(
                new FLREndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $busContext->sendLocal(new MyMessage());
                }
            )
            ->run();

        /** @var FLRContext $context */
        $context = $result->getScenarioContext();
        $this->assertSame(5 + 1, $context->retryCount, "Message should only be retried 5 times.");
        $this->assertSame(
            1,
            $result->getErrorMessageCountInQueueOf(FLREndpoint::class),
            "Message should be in the error queue if all retries have failed."
        );
    }
}

namespace acceptance\PSB\Core\ErrorHandling\FirstLevelRetry\WhenDoingFlrWithDefaultSettingsTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;

class FLRContext extends ScenarioContext
{
    public $retryCount;
}

class FLREndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(new Container([MessageHandler::class => new MessageHandler($this->scenarioContext)]));
        $this->registerCommandHandler(MyMessage::class, MessageHandler::class);
    }
}

class MessageHandler implements MessageHandlerInterface
{
    /** @var FLRContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->retryCount++;

        if ($this->scenarioContext->retryCount == 6) {
            $context->shutdownThisEndpointAfterCurrentMessage();
        }

        throw new \Exception('simulated');
    }
}

class MyMessage
{
}
