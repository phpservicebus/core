<?php
namespace PSB\Core\Feature;


use PSB\Core\BusContextInterface;
use PSB\Core\ObjectBuilder\BuilderInterface;

class FeatureStartupTaskController
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
     * @param BuilderInterface    $builder
     * @param BusContextInterface $busContext
     */
    public function start(BuilderInterface $builder, BusContextInterface $busContext)
    {
        $taskFactory = $this->taskFactory;
        /** @var FeatureStartupTaskInterface $startupTask */
        $startupTask = $taskFactory($builder);
        $startupTask->start($busContext);
    }
}
