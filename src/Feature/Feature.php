<?php
namespace PSB\Core\Feature;


use PSB\Core\BusContextInterface;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Guard;
use PSB\Core\Util\Settings;

abstract class Feature
{
    /**
     * @var array
     */
    private $dependencies = [];

    /**
     * @var bool
     */
    private $isEnabledByDefault = false;

    /**
     * @var bool
     */
    private $isActive = false;

    /**
     * @var callable[]
     */
    private $registeredDefaults = [];

    /**
     * @var Prerequisite[]
     */
    private $registeredPrerequisites = [];

    /**
     * @var FeatureStartupTaskController[]
     */
    private $registeredStartupTasksControllers = [];

    /**
     * @var FeatureInstallTaskController[]
     */
    private $registeredInstallTasksControllers = [];

    /**
     * Method will always be executed and should be used to determine whether to enable or disable the feature,
     * configure default settings, configure dependencies, configure prerequisites and register startup tasks.
     */
    abstract public function describe();

    /**
     * Method is called if all defined conditions are met and the feature is marked as enabled.
     * Use this method to configure and initialize all required components for the feature like
     * the steps in the pipeline or the instances/factories in the container.
     *
     * @param Settings              $settings
     * @param BuilderInterface      $builder
     * @param PipelineModifications $pipelineModifications
     */
    abstract public function setup(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    );

    /**
     * @param Settings              $settings
     * @param BuilderInterface      $builder
     * @param PipelineModifications $pipelineModifications
     *
     * @return bool
     */
    public function checkPrerequisites(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        foreach ($this->registeredPrerequisites as $prerequisite) {
            $condition = $prerequisite->getCondition();
            if (!$condition($settings, $builder, $pipelineModifications)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Settings              $settings
     * @param BuilderInterface      $builder
     * @param PipelineModifications $pipelineModifications
     */
    public function activate(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $this->setup($settings, $builder, $pipelineModifications);
        $this->isActive = true;
    }

    /**
     * @param BuilderInterface    $builder
     * @param BusContextInterface $busContext
     */
    public function start(BuilderInterface $builder, BusContextInterface $busContext)
    {
        foreach ($this->registeredStartupTasksControllers as $controller) {
            $controller->start($builder, $busContext);
        }
    }

    /**
     * @param BuilderInterface $builder
     */
    public function install(BuilderInterface $builder)
    {
        foreach ($this->registeredInstallTasksControllers as $controller) {
            $controller->install($builder);
        }
    }

    /**
     * @param Settings $settings
     */
    public function configureDefaults(Settings $settings)
    {
        foreach ($this->registeredDefaults as $registeredDefault) {
            $registeredDefault($settings);
        }
    }

    /**
     * @param callable $defaultInitializer
     */
    protected function registerDefault(callable $defaultInitializer)
    {
        $this->registeredDefaults[] = $defaultInitializer;
    }

    /**
     * @param callable $prerequisiteSpecification
     * @param string   $description
     */
    protected function registerPrerequisite(callable $prerequisiteSpecification, $description)
    {
        Guard::againstNullAndEmpty('description', $description);
        $this->registeredPrerequisites[] = new Prerequisite($prerequisiteSpecification, $description);
    }

    /**
     * @param callable $taskFactory
     */
    protected function registerStartupTask(callable $taskFactory)
    {
        $this->registeredStartupTasksControllers[] = new FeatureStartupTaskController($taskFactory);
    }

    /**
     * @param callable $taskFactory
     */
    protected function registerInstallTask(callable $taskFactory)
    {
        $this->registeredInstallTasksControllers[] = new FeatureInstallTaskController($taskFactory);
    }

    protected function enableByDefault()
    {
        $this->isEnabledByDefault = true;
    }

    /**
     * @param string $featureFqcn
     */
    protected function dependsOn($featureFqcn)
    {
        $this->dependencies[] = [$featureFqcn];
    }

    /**
     * @param array $featureFqcns
     */
    protected function dependsOnAtLeastOne(array $featureFqcns)
    {
        $this->dependencies[] = $featureFqcns;
    }

    /**
     * @param string $featureFqcn
     */
    protected function dependsOnOptionally($featureFqcn)
    {
        $this->dependsOnAtLeastOne([RootFeature::class, $featureFqcn]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::class;
    }

    /**
     * @return bool
     */
    public function isEnabledByDefault()
    {
        return $this->isEnabledByDefault;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }
}
