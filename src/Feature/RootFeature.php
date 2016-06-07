<?php
namespace PSB\Core\Feature;


use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Util\Settings;

class RootFeature extends Feature
{
    public function describe()
    {
        $this->enableByDefault();
    }

    /**
     * @param Settings              $settings
     * @param BuilderInterface      $builder
     * @param PipelineModifications $pipelineModifications
     */
    public function setup(Settings $settings, BuilderInterface $builder, PipelineModifications $pipelineModifications)
    {
    }
}
