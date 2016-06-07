<?php
namespace acceptance\PSB\Core\Serialization;


use acceptance\PSB\Core\Serialization\WhenUsingACustomSerializerTest\MyCommand;
use acceptance\PSB\Core\Serialization\WhenUsingACustomSerializerTest\SerializationContext;
use acceptance\PSB\Core\Serialization\WhenUsingACustomSerializerTest\SerializationEndpoint;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given SerializationEndpoint
 * And a command MyCommand
 * And endpoint uses MySerializationDefinition
 *
 * When SerializationEndpoint sends MyCommand locally
 *
 * Then SerializationEndpoint should receive it
 */
class WhenUsingACustomSerializerTest extends ScenarioTestCase
{
    public function testShouldReceiveTheMessage()
    {
        $result = $this->scenario
            ->givenContext(SerializationContext::class)
            ->givenEndpoint(
                new SerializationEndpoint(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $busContext->send(new MyCommand());
                }
            )
            ->run();

        /** @var SerializationContext $context */
        $context = $result->getScenarioContext();
        $this->assertTrue($context->receivedTheMessage, "Endpoint should have received the message.");
        $this->assertTrue(
            $context->serializerInvokedForSerialization,
            "Serializer should have been invoked for message serialization."
        );
        $this->assertTrue(
            $context->serializerInvokedForDeserialization,
            "Serializer should have been invoked for message deserialization."
        );
    }
}

namespace acceptance\PSB\Core\Serialization\WhenUsingACustomSerializerTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\ContentTypeEnum;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;
use PSB\Core\Serialization\MessageSerializerInterface;
use PSB\Core\Serialization\SerializationConfigurator;
use PSB\Core\Serialization\SerializationDefinition;
use PSB\Core\Util\Settings;

class SerializationContext extends ScenarioContext
{
    public $receivedTheMessage;
    public $serializerInvokedForSerialization;
    public $serializerInvokedForDeserialization;
}

class SerializationEndpoint extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(new Container([ReceivingHandler::class => new ReceivingHandler($this->scenarioContext)]));
        $this->registerCommandRoutingRule(MyCommand::class, self::class);
        $this->registerCommandHandler(MyCommand::class, ReceivingHandler::class);
        $this->useSerialization(new MySerializationDefinition($this->scenarioContext));
    }
}

class ReceivingHandler implements MessageHandlerInterface
{
    /** @var SerializationContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function handle($message, MessageHandlerContextInterface $context)
    {
        $this->scenarioContext->receivedTheMessage = true;
        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class MySerializationDefinition extends SerializationDefinition
{
    /** @var SerializationContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function createConfigurator(Settings $settings)
    {
        return new MySerializationConfigurator($settings);
    }

    public function formalize(Settings $settings)
    {
        return function () {
            return new MySerializer($this->scenarioContext);
        };
    }
}

class MySerializationConfigurator extends SerializationConfigurator
{
}

class MySerializer implements MessageSerializerInterface
{
    /** @var SerializationContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function serialize($message)
    {
        $this->scenarioContext->serializerInvokedForSerialization = true;
        return serialize($message);
    }

    public function deserialize($string, $messageType)
    {
        $this->scenarioContext->serializerInvokedForDeserialization = true;
        return unserialize($string);
    }

    public function getContentType()
    {
        return ContentTypeEnum::BINARY;
    }
}

class MyCommand
{
}
