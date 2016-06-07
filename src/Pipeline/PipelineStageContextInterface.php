<?php
namespace PSB\Core\Pipeline;


use PSB\Core\ObjectBuilder\BuilderInterface;

interface PipelineStageContextInterface
{
    /**
     * @return BuilderInterface
     */
    public function getBuilder();
}
