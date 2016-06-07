<?php
namespace PSB\Core\Pipeline;


use PSB\Core\Util\ContextBag;
use PSB\Core\ObjectBuilder\BuilderInterface;

abstract class PipelineStageContext extends ContextBag implements PipelineStageContextInterface
{
    /**
     * @param PipelineStageContext|null $parentContext
     */
    public function __construct(PipelineStageContext $parentContext = null)
    {
        parent::__construct($parentContext);
    }

    /**
     * @return BuilderInterface
     */
    public function getBuilder()
    {
        return $this->get(BuilderInterface::class);
    }
}
