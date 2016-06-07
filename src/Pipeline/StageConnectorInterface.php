<?php
namespace PSB\Core\Pipeline;

interface StageConnectorInterface extends PipelineStepInterface
{
    /**
     * @return string
     */
    public static function getNextStageContextClass();
}
