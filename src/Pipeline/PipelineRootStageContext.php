<?php
namespace PSB\Core\Pipeline;


use PSB\Core\ObjectBuilder\BuilderInterface;

class PipelineRootStageContext extends PipelineStageContext
{
    public function __construct(BuilderInterface $builder)
    {
        parent::__construct(null);
        $this->set(BuilderInterface::class, $builder);
    }
}
