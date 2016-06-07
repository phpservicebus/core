<?php
namespace acceptance\PSB\Core\Correlation;


use acceptance\PSB\Core\Correlation\WhenReplyingToReceivedMessageWithoutCorrelationIdTest\CorrelationContext;
use acceptance\PSB\Core\Correlation\WhenReplyingToReceivedMessageWithoutCorrelationIdTest\CorrelationEndpoint;
use acceptance\PSB\Core\Correlation\WhenReplyingToReceivedMessageWithoutCorrelationIdTest\MyRequest;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;
use PSB\Core\SendOptions;

/**
 * Given CorrelationEndpoint
 * And messages MyRequest and MyResponse
 * And MyRequestMessageHandler that handles MyRequest
 * And MyResponseMessageHandler that handles MyResponse
 * And an incoming physical message mutator CorrelationIdRemover which removes the correlation id
 *
 * When CorrelationEndpoint sends MyRequest locally using a custom id
 * And sends MyResponse locally on receiving MyRequest
 *
 * Then the received MyResponse correlation id should be the custom id
 */
class WhenReplyingToReceivedMessageWithoutCorrelationIdTest extends ScenarioTestCase
{
    public function testShouldUseTheIncomingMessageIdAsTheCorrelationId()
    {
        $customId = md5('just some id');
        $result = $this->scenario
            ->givenContext(CorrelationContext::class)
            ->givenEndpoint(
                new CorrelationEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) use ($customId) {
                    $options = new SendOptions();
                    $options->setMessageId($customId);
                    $busContext->sendLocal(new MyRequest(), $options);
                }
            )
            ->run();

        /** @var CorrelationContext $context */
        $context = $result->getScenarioContext();
        $this->assertSame(
            $customId,
            $context->correlationIdReceived,
            "Response correlation id should be the request id."
        );
    }
}

namespace acceptance\PSB\Core\Correlation\WhenReplyingToReceivedMessageWithoutCorrelationIdTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\MessageIntentEnum;
use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingPhysicalMessageMutationContext;
use PSB\Core\MessageMutation\Pipeline\Incoming\IncomingPhysicalMessageMutatorInterface;
use PSB\Core\ObjectBuilder\Container;

class CorrelationContext extends ScenarioContext
{
    public $correlationIdReceived;
}

class CorrelationEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container(
                [
                    MyRequestHandler::class => new MyRequestHandler(),
                    MyResponseHandler::class => new MyResponseHandler($this->scenarioContext),
                    CorrelationIdRemover::class => new CorrelationIdRemover()
                ]
            )
        );
        $this->registerCommandHandler(MyRequest::class, MyRequestHandler::class);
        $this->registerCommandHandler(MyResponse::class, MyResponseHandler::class);
        $this->registerIncomingPhysicalMessageMutator(CorrelationIdRemover::class);
    }
}

class MyRequestHandler implements MessageHandlerInterface
{
    public function handle($message, MessageHandlerContextInterface $context)
    {
        $context->reply(new MyResponse());
    }
}

class MyResponseHandler implements MessageHandlerInterface
{
    /** @var CorrelationContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->correlationIdReceived = $context->getHeaders()[HeaderTypeEnum::CORRELATION_ID];
        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class CorrelationIdRemover implements IncomingPhysicalMessageMutatorInterface
{
    public function mutateIncoming(IncomingPhysicalMessageMutationContext $context)
    {
        if ($context->getHeaders()[HeaderTypeEnum::MESSAGE_INTENT] != MessageIntentEnum::REPLY) {
            $context->setHeader(HeaderTypeEnum::CORRELATION_ID, '');
        }
    }
}

class MyRequest
{
}

class MyResponse
{
}
