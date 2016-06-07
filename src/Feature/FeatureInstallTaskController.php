<?php
namespace PSB\Core\Feature;


use PSB\Core\ObjectBuilder\BuilderInterface;

class FeatureInstallTaskController
{
    /**
     * @var callable
     */
    private $taskFactory;

    /**
     * @param callable $taskFactory
     */
    public function __construct(callable $taskFactory)
    {
        $this->taskFactory = $taskFactory;
    }

    /**
     * @param BuilderInterface $builder
     */
    public function install(BuilderInterface $builder)
    {
        $taskFactory = $this->taskFactory;
        /** @var FeatureInstallTaskInterface $installTask */
        $installTask = $taskFactory($builder);
        $installTask->install();
    }
}
