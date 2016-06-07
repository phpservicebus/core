<?php
namespace PSB\Core\Transport;


use PSB\Core\Feature\Feature;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Transport\Config\TransportInfrastructure;
use PSB\Core\Util\Settings;

class TransportFeature extends Feature
{

    /**
     * Method will always be executed and should be used to determine whether to enable or disable the feature,
     * configure default settings, configure dependencies, configure prerequisites and register startup tasks.
     */
    public function describe()
    {
        $this->enableByDefault();
        $this->registerDefault(
            function (Settings $settings) {
                /** @var TransportInfrastructure $transportInfrastructure */
                $transportInfrastructure = $settings->get(TransportInfrastructure::class);
                $settings->setDefault(
                    KnownSettingsEnum::LOCAL_ADDRESS,
                    $transportInfrastructure->toTransportAddress($settings->get(KnownSettingsEnum::ENDPOINT_NAME))
                );
            }
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
    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {

    }
}
