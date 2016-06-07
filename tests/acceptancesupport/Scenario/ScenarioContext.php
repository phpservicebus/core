<?php
namespace acceptancesupport\PSB\Core\Scenario;


class ScenarioContext
{
    /**
     * @var array
     */
    private $traces = [];

    /**
     * @param string $trace
     */
    public function addTrace($trace)
    {
        $this->traces[] = $trace;
    }

    /**
     * @return array
     */
    public function getTraces()
    {
        return $this->traces;
    }
}
