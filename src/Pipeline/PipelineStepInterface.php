<?php
namespace PSB\Core\Pipeline;


interface PipelineStepInterface
{
    /**
     * @param PipelineStageContextInterface $context
     * @param callable                      $next
     */
    public function invoke($context, callable $next);

    /**
     * @return string
     */
    public static function getStageContextClass();
}