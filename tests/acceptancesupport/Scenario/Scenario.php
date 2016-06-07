<?php
namespace acceptancesupport\PSB\Core\Scenario;


class Scenario
{
    /**
     * @param string $contextFqcn
     *
     * @return ScenarioBuilder
     */
    public static function define($contextFqcn)
    {
        $scenarioBuilder = new ScenarioBuilder($contextFqcn);
        return $scenarioBuilder;
    }
}
