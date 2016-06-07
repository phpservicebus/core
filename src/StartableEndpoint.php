<?php
namespace PSB\Core;


use PSB\Core\Feature\Feature;
use PSB\Core\Feature\FeatureActivator;
use PSB\Core\Feature\FeatureInstaller;
use PSB\Core\Feature\FeatureStarter;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Persistence\PersistenceDefinitionApplier;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Transport\Config\TransportDefinition;
use PSB\Core\Transport\Config\TransportInfrastructure;
use PSB\Core\Transport\TransportReceiver;
use PSB\Core\Util\Settings;

class StartableEndpoint
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var PersistenceDefinitionApplier
     */
    private $persistenceDefinitionApplier;

    /**
     * @var BuilderInterface
     */
    private $builder;

    /**
     * @var PipelineModifications
     */
    private $pipelineModifications;

    /**
     * @var BusContextInterface
     */
    private $busContext;

    /**
     * @var bool
     */
    private $isPrepared = false;


    /**
     * @param Settings                     $settings
     * @param PersistenceDefinitionApplier $persistenceDefinitionApplier
     * @param BuilderInterface             $builder
     * @param PipelineModifications        $pipelineModifications
     * @param BusContextInterface          $busContext
     */
    public function __construct(
        Settings $settings,
        PersistenceDefinitionApplier $persistenceDefinitionApplier,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications,
        BusContextInterface $busContext
    ) {
        $this->settings = $settings;
        $this->persistenceDefinitionApplier = $persistenceDefinitionApplier;
        $this->builder = $builder;
        $this->pipelineModifications = $pipelineModifications;
        $this->busContext = $busContext;
    }


    /**
     * @return StartableEndpoint
     */
    public function prepare()
    {
        $this->persistenceDefinitionApplier->apply($this->settings);

        $featureActivator = new FeatureActivator($this->settings);

        foreach ($this->settings->get(KnownSettingsEnum::FEATURE_FQCN_LIST) as $featureFqcn) {
            /** @var Feature $feature */
            $feature = new $featureFqcn();
            $feature->describe();
            $featureActivator->addFeature($feature);
        }

        $featureInstaller = new FeatureInstaller($featureActivator->getFeatures());
        $featureStarter = new FeatureStarter($featureActivator->getFeatures());


        /** @var TransportDefinition $transportDefinition */
        $transportDefinition = $this->settings->get(TransportDefinition::class);
        $this->settings->set(
            TransportInfrastructure::class,
            $transportDefinition->formalize(
                $this->settings,
                $transportDefinition->createConnectionFactory($this->settings)
            )
        );

        $featureActivator->activateFeatures($this->builder, $this->pipelineModifications);

        $featureInstaller->installFeatures($this->builder, $this->settings);

        $this->pipelineModifications->registerStepsInBuilder($this->builder);

        $featureStarter->startFeatures($this->builder, $this->busContext);

        $this->isPrepared = true;

        return $this;
    }

    /**
     * @return EndpointInstance
     */
    public function start()
    {
        if (!$this->isPrepared) {
            $this->prepare();
        }

        if (!$this->settings->tryGet(KnownSettingsEnum::SEND_ONLY)) {
            /** @var TransportReceiver $transportReceiver */
            $transportReceiver = $this->builder->build(TransportReceiver::class);
            $transportReceiver->start();
        }

        return new EndpointInstance($this->busContext);
    }

    /**
     * @return BusContextInterface
     */
    public function getBusContext()
    {
        return $this->busContext;
    }

    /**
     * @return boolean
     */
    public function isIsPrepared()
    {
        return $this->isPrepared;
    }

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return BuilderInterface
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @return PipelineModifications
     */
    public function getPipelineModifications()
    {
        return $this->pipelineModifications;
    }
}
