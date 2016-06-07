<?php
namespace PSB\Core\Feature;


use PSB\Core\Util\DependencyGraph\DependencyGraph;
use PSB\Core\Util\DependencyGraph\GraphBuilderInterface;

class FeatureDependencyGraphBuilder implements GraphBuilderInterface
{

    /**
     * @var Feature[]
     */
    private $features;

    /**
     * @var Feature[]
     */
    private $fqcnToFeature = [];

    /**
     * The actual graph maintained as an array of arrays (node fqcn to successor fqcns).
     *
     * @var [][]
     */
    private $fqcnDirectedGraph = [];

    /**
     * @param Feature[] $features
     */
    public function __construct($features)
    {
        $this->features = $features;
    }

    /**
     * @return DependencyGraph
     */
    public function build()
    {
        // prepare the directed graph lists and fqcn to feature mapping
        foreach ($this->features as $feature) {
            $this->fqcnDirectedGraph[$feature->getName()] = [];
            $this->fqcnToFeature[$feature->getName()] = $feature;
        }

        // build the actual graph from feature dependencies
        foreach ($this->features as $feature) {
            $flattenedDependencies = [];
            if ($feature->getDependencies()) {
                $flattenedDependencies = array_unique(call_user_func_array('array_merge', $feature->getDependencies()));
            }

            foreach ($flattenedDependencies as $dependency) {
                if (isset($this->fqcnToFeature[$dependency])) {
                    $this->fqcnDirectedGraph[$dependency][] = $feature->getName();
                }
            }
        }

        return new DependencyGraph($this->fqcnToFeature, $this->fqcnDirectedGraph);
    }
}
