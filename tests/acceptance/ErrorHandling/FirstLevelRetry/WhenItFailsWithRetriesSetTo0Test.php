<?php
namespace acceptance\PSB\Core\ErrorHandling\FirstLevelRetry;


use acceptance\PSB\Core\ErrorHandling\FirstLevelRetry\WhenItFailsWithRetriesSetTo0Test\FLRContext;
use acceptance\PSB\Core\ErrorHandling\FirstLevelRetry\WhenItFailsWithRetriesSetTo0Test\FLREndpoint;
use acceptance\PSB\Core\ErrorHandling\FirstLevelRetry\WhenItFailsWithRetriesSetTo0Test\MyMessage;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

class WhenItFailsWithRetriesSetTo0Test extends ScenarioTestCase
{
    /**
     * Given FLREndpoint
     * And message MyMessage
     * And handler MyMessageHandler
     * And FLREndpoint configured with 0 retries for FLR
     *
     * When FLREndpoint sends MyMessage locally
     * And MyMessageHandler blows up
     *
     * Then MyMessage should no be retried
     * And MyMessage should be sent to the error queue
     */
    public function testShouldNotRetryAndMoveToErrorQueue()
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
        $this->assertSame(1, $context->retryCount, "Message should not be retried.");
        $this->assertSame(
            1,
            $result->getErrorMessageCountInQueueOf(FLREndpoint::class),
            "Message should be in the error queue if it has failed."
        );
    }
}

namespace acceptance\PSB\Core\ErrorHandling\FirstLevelRetry\WhenItFailsWithRetriesSetTo0Test;

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
        $this->setMaxFirstLevelRetries(0);
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

        $context->shutdownThisEndpointAfterCurrentMessage();

        throw new \Exception('simulated');
    }
}

class MyMessage
{
}
