<?php
namespace PSB\Core\Feature;


use PSB\Core\BusContextInterface;

interface FeatureStartupTaskInterface
{
    /**
     * @param BusContextInterface $busContext
     *
     * @return void
     */
    public function start(BusContextInterface $busContext);
}
