<?php
namespace acceptance\PSB\Core\Routing;


use acceptance\PSB\Core\Routing\WhenReplyingToAMessageTest\RoutingContext;
use acceptance\PSB\Core\Routing\WhenReplyingToAMessageTest\MyMessage;
use acceptance\PSB\Core\Routing\WhenReplyingToAMessageTest\OtherEndpoint;
use acceptance\PSB\Core\Routing\WhenReplyingToAMessageTest\ReplyingEndpoint;
use acceptance\PSB\Core\Routing\WhenReplyingToAMessageTest\ReplyingToOtherEndpoint;
use acceptance\PSB\Core\Routing\WhenReplyingToAMessageTest\SendingEndpoint;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;
use PSB\Core\SendOptions;

class WhenReplyingToAMessageTest extends ScenarioTestCase
{
    /**
     * Given SendingEndpoint, ReplyingEndpoint and OtherEndpoint
     * And messages MyMessage and MyReply
     *
     * When SendingEndpoint sends MyMessage to ReplyingEndpoint
     *
     * Then ReplyingEndpoint should send MyReply back to SendingEndpoint
     * And SendingEndpoint should receive MyReply
     * And OtherEndpoint should receive nothing
     */
    public function testShouldReplyToOriginator()
    {
        $result = $this->scenario
            ->givenContext(RoutingContext::class)
            ->givenEndpoint(
                new SendingEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $context->waitForGoFrom(ReplyingEndpoint::class);
                    $context->waitForGoFrom(OtherEndpoint::class);
                    $busContext->send(new MyMessage());
                }
            )
            ->givenEndpoint(
                new ReplyingEndpoint(),
                function (RunContext $context) {
                    $context->go();
                }
            )
            ->givenEndpoint(
                new OtherEndpoint(),
                function (RunContext $context) {
                    $context->go();
                    die;
                }
            )
            ->run();

        /** @var RoutingContext $context */
        $context = $result->getScenarioContext();
        $this->assertTrue($context->sendingEndpointGotReponse);
        $this->assertNull($context->otherEndpointGotReponse);
        $this->assertSame(0, $result->getMessageCountInQueueOf(OtherEndpoint::class));
    }

    /**
     * Given SendingEndpoint, ReplyingToOtherEndpoint and OtherEndpoint
     * And messages MyMessage and MyReply
     * And ReplyingToOtherEndpoint is configured to reply to OtherEndpoint when receiving MyMessage
     *
     * When SendingEndpoint sends MyMessage to ReplyingToOtherEndpoint
     *
     * Then ReplyingToOtherEndpoint should send MyReply to OtherEndpoint
     * And OtherEndpoint should receive MyReply
     * And SendingEndpoint should receive nothing
     */
    public function testShouldReplyToConfiguredReturnAddress()
    {
        $result = $this->scenario
            ->givenContext(RoutingContext::class)
            ->givenEndpoint(
                new SendingEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $context->waitForGoFrom(ReplyingToOtherEndpoint::class);
                    $context->waitForGoFrom(OtherEndpoint::class);
                    $options = new SendOptions();
                    $options->setExplicitDestination(ReplyingToOtherEndpoint::class);
                    $busContext->send(new MyMessage(), $options);
                    die;
                }
            )
            ->givenEndpoint(
                new ReplyingToOtherEndpoint(),
                function (RunContext $context) {
                    $context->go();
                }
            )
            ->givenEndpoint(
                new OtherEndpoint(),
                function (RunContext $context) {
                    $context->go();
                }
            )
            ->run();

        /** @var RoutingContext $context */
        $context = $result->getScenarioContext();
        $this->assertNull($context->sendingEndpointGotReponse);
        $this->assertSame(0, $result->getMessageCountInQueueOf(SendingEndpoint::class));
        $this->assertTrue($context->otherEndpointGotReponse);
    }
}

namespace acceptance\PSB\Core\Routing\WhenReplyingToAMessageTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;
use PSB\Core\ReplyOptions;

class RoutingContext extends ScenarioContext
{
    public $sendingEndpointGotReponse;
    public $otherEndpointGotReponse;
}

class SendingEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container(
                [
                    SenderResponseHandler::class => new SenderResponseHandler($this->scenarioContext)
                ]
            )
        );
        $this->registerCommandHandler(MyReply::class, SenderResponseHandler::class);
        $this->registerCommandRoutingRule(MyMessage::class, ReplyingEndpoint::class);
    }
}

class SenderResponseHandler implements MessageHandlerInterface
{
    /** @var RoutingContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->sendingEndpointGotReponse = true;

        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class ReplyingEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container(
                [
                    MessageHandler::class => new MessageHandler()
                ]
            )
        );
        $this->registerCommandHandler(MyMessage::class, MessageHandler::class);
    }
}

class MessageHandler implements MessageHandlerInterface
{
    public function handle($message, MessageHandlerContextInterface $context)
    {
        $context->reply(new MyReply());

        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class OtherEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container(
                [
                    OtherResponseHandler::class => new OtherResponseHandler($this->scenarioContext)
                ]
            )
        );
        $this->registerCommandHandler(MyReply::class, OtherResponseHandler::class);
    }
}

class OtherResponseHandler implements MessageHandlerInterface
{
    /** @var RoutingContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->otherEndpointGotReponse = true;

        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class ReplyingToOtherEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(
            new Container(
                [
                    ConfiguredReplyAddressMessageHandler::class => new ConfiguredReplyAddressMessageHandler()
                ]
            )
        );
        $this->registerCommandHandler(MyMessage::class, ConfiguredReplyAddressMessageHandler::class);
    }
}

class ConfiguredReplyAddressMessageHandler implements MessageHandlerInterface
{
    public function handle($message, MessageHandlerContextInterface $context)
    {
        $replyOptions = new ReplyOptions();
        $replyOptions->overrideReplyToAddressOfIncomingMessage(OtherEndpoint::class);
        $context->reply(new MyReply(), $replyOptions);

        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class MyMessage
{
}


class MyReply
{
}
