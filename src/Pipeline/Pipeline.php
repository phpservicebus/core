<?php
namespace PSB\Core\Pipeline;


class Pipeline implements PipelineInterface
{
    /**
     * @var PipelineStepInterface[]
     */
    private $stepList;

    /**
     * @param PipelineStepInterface[] $stepList
     */
    public function __construct(array $stepList)
    {
        $this->stepList = $stepList;
    }

    /**
     * @param PipelineStageContextInterface $context
     */
    public function invoke(PipelineStageContextInterface $context)
    {
        $this->invokeNext($context, 0);
    }

    /**
     * @param PipelineStageContextInterface $context
     * @param int                           $currentIndex
     */
    private function invokeNext(PipelineStageContextInterface $context, $currentIndex)
    {
        if ($currentIndex == count($this->stepList)) {
            return;
        }

        $step = $this->stepList[$currentIndex];

        if ($step instanceof StageConnectorInterface) {
            $step->invoke(
                $context,
                function ($newContext) use ($currentIndex) {
                    $this->invokeNext($newContext, $currentIndex + 1);
                }
            );
        } else {
            $step->invoke(
                $context,
                function () use ($context, $currentIndex) {
                    $this->invokeNext($context, $currentIndex + 1);
                }
            );
        }
    }
}
