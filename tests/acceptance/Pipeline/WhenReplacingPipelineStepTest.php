<?php
namespace acceptance\PSB\Core\Pipeline;


use acceptance\PSB\Core\Pipeline\WhenReplacingPipelineStepTest\EndpointWithReplacement;
use acceptance\PSB\Core\Pipeline\WhenReplacingPipelineStepTest\MyMessage;
use acceptance\PSB\Core\Pipeline\WhenReplacingPipelineStepTest\PipelineContext;
use acceptancesupport\PSB\Core\PhpUnit\ScenarioTestCase;
use acceptancesupport\PSB\Core\Scenario\RunContext;
use PSB\Core\BusContextInterface;

/**
 * Given EndpointWithReplacement
 * And message MyMessage
 * And pipeline steps OriginalPipelineStep and ReplacementPipelineStep
 * And ReplacementPipelineStep replaces OriginalPipelineStep
 *
 * When NormalEndpoint sends MyMessage locally
 *
 * Then ReplacementPipelineStep should be invoked
 * And OriginalPipelineStep should not be invoked
 */
class WhenReplacingPipelineStepTest extends ScenarioTestCase
{
    public function testShouldInvokeReplacementInPipeline()
    {
        $result = $this->scenario
            ->givenContext(PipelineContext::class)
            ->givenEndpoint(
                new EndpointWithReplacement(),
                function (RunContext $context, BusContextInterface $busContext) {
                    $busContext->sendLocal(new MyMessage());
                }
            )
            ->run();

        /** @var PipelineContext $context */
        $context = $result->getScenarioContext();
        $this->assertTrue($context->replacementStepInvoked, "Replacement pipeline step should have been invoked.");
        $this->assertNull($context->originalStepInvoked, "Original replacment step should no have been invoked.");
    }
}

namespace acceptance\PSB\Core\Pipeline\WhenReplacingPipelineStepTest;

use acceptancesupport\PSB\Core\Scenario\EndpointConfiguratorProxy;
use acceptancesupport\PSB\Core\Scenario\ScenarioContext;
use PSB\Core\MessageHandlerContextInterface;
use PSB\Core\MessageHandlerInterface;
use PSB\Core\ObjectBuilder\Container;
use PSB\Core\Pipeline\Incoming\StageContext\TransportReceiveContext;
use PSB\Core\Pipeline\PipelineStepInterface;

class PipelineContext extends ScenarioContext
{
    public $originalStepInvoked;
    public $replacementStepInvoked;
}

class EndpointWithReplacement extends EndpointConfiguratorProxy
{
    public function init()
    {
        $this->useContainer(new Container([MyMessageHandler::class => new MyMessageHandler()]));
        $this->registerCommandHandler(MyMessage::class, MyMessageHandler::class);

        // replace before register to ensure out-of-order replacements work correctly
        $this->replacePipelineStep(
            'OriginalPipelineStep',
            ReplacementPipelineStep::class,
            function () {
                return new ReplacementPipelineStep($this->scenarioContext);
            }
        );
        $this->registerPipelineStep(
            'OriginalPipelineStep',
            OriginalPipelineStep::class,
            function () {
                return new OriginalPipelineStep($this->scenarioContext);
            }
        );
    }
}

class MyMessageHandler implements MessageHandlerInterface
{
    public function handle($message, MessageHandlerContextInterface $context)
    {
        $context->shutdownThisEndpointAfterCurrentMessage();
    }
}

class OriginalPipelineStep implements PipelineStepInterface
{
    /** @var PipelineContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function invoke($context, callable $next)
    {
        $this->scenarioContext->originalStepInvoked = true;
        $next();
    }

    public static function getStageContextClass()
    {
        return TransportReceiveContext::class;
    }
}

class ReplacementPipelineStep implements PipelineStepInterface
{
    /** @var PipelineContext */
    public $scenarioContext;

    public function __construct($scenarioContext)
    {
        $this->scenarioContext = $scenarioContext;
    }

    public function invoke($context, callable $next)
    {
        $this->scenarioContext->replacementStepInvoked = true;
        $next();
    }

    public static function getStageContextClass()
    {
        return TransportReceiveContext::class;
    }
}

class MyMessage
{
}
