<?php
namespace acceptancesupport\PSB\Core\Scenario;


use PSB\Core\EndpointConfigurator;

interface EndpointTestExecutionConfiguratorInterface
{
    /**
     * @param EndpointConfigurator $endpointConfigurator
     */
    public function configure(EndpointConfigurator $endpointConfigurator);

    public function cleanup();
}
