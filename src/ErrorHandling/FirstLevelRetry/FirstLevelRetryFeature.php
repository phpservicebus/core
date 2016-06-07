<?php
namespace PSB\Core\ErrorHandling\FirstLevelRetry;


use PSB\Core\ErrorHandling\FirstLevelRetry\Pipeline\FirstLevelRetryPipelineStep;
use PSB\Core\Feature\Feature;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class FirstLevelRetryFeature extends Feature
{
    const DEFAULT_MAX_RETRIES = 5;

    /**
     * Method will always be executed and should be used to determine whether to enable or disable the feature,
     * configure default settings, configure dependencies, configure prerequisites and register startup tasks.
     */
    public function describe()
    {
        $this->enableByDefault();
        $this->registerPrerequisite(
            function (Settings $settings) {
                return !$settings->tryGet(KnownSettingsEnum::SEND_ONLY);
            },
            "Send only endpoints can't use FLR since it only applies to messages being received."
        );
        $this->registerPrerequisite(
            function (Settings $settings) {
                return $this->getMaxRetries($settings) > 0;
            },
            "FLR was disabled in config since it's set to 0."
        );
    }

    /**
     * Method is called if all defined conditions are met and the feature is marked as enabled.
     * Use this method to configure and initialize all required components for the feature like
     * the steps in the pipeline or the instances/factories in the container.
     *
     * @param Settings              $settings
     * @param BuilderInterface      $builder
     * @param PipelineModifications $pipelineModifications
     */
    public function setup(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $maxRetries = $this->getMaxRetries($settings);

        $builder->defineSingleton(FirstLevelRetryStorage::class, new FirstLevelRetryStorage());
        $builder->defineSingleton(FirstLevelRetryPolicy::class, new FirstLevelRetryPolicy($maxRetries));

        $pipelineModifications->registerStep(
            'FirstLevelRetryPipelineStep',
            FirstLevelRetryPipelineStep::class,
            function () use ($builder) {
                return new FirstLevelRetryPipelineStep(
                    $builder->build(FirstLevelRetryStorage::class),
                    $builder->build(FirstLevelRetryPolicy::class)
                );
            }
        );
    }

    /**
     * @param Settings $settings
     *
     * @return int
     */
    private function getMaxRetries(Settings $settings)
    {
        $maxRetries = $settings->tryGet(KnownSettingsEnum::MAX_FLR_RETRIES);
        $maxRetries = $maxRetries === null ? self::DEFAULT_MAX_RETRIES : $maxRetries;
        return $maxRetries;
    }
}
