<?php
namespace PSB\Core\Routing\AutoSubscription;


use PSB\Core\Feature\Feature;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\MessageHandlerRegistry;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class AutoSubscribeFeature extends Feature
{

    public function describe()
    {
        $this->enableByDefault();
        $this->registerPrerequisite(
            function (Settings $settings) {
                return !$settings->tryGet(KnownSettingsEnum::SEND_ONLY);
            },
            "Send only endpoints can't autosubscribe."
        );
    }

    /**
     * @param Settings              $settings
     * @param BuilderInterface      $builder
     * @param PipelineModifications $pipelineModifications
     */
    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
        $this->registerStartupTask(
            function (BuilderInterface $builder) {
                /** @var MessageHandlerRegistry $handlerRegistry */
                $handlerRegistry = $builder->build(MessageHandlerRegistry::class);
                return new SubscriptionApplierStartupTask($handlerRegistry->getEventFqcns());
            }
        );
    }
}
