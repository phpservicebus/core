<?php

namespace PSB\Core\Feature;

use PSB\Core\BusContextInterface;
use PSB\Core\ObjectBuilder\BuilderInterface;

class FeatureStarter
{
    /**
     * @var Feature[]
     */
    private $features;

    /**
     * @param Feature[] $features
     */
    public function __construct(array $features)
    {
        $this->features = $features;
    }

    /**
     * @param BuilderInterface    $builder
     * @param BusContextInterface $busContext
     */
    public function startFeatures(BuilderInterface $builder, BusContextInterface $busContext)
    {
        foreach ($this->features as $feature) {
            if ($feature->isActive()) {
                $feature->start($builder, $busContext);
            }
        }
    }
}
