<?php
namespace acceptancesupport\PSB\Core\Scenario;


interface EndpointQueuesInformationProviderInterface
{
    /**
     * @param string $endpointFqcn
     *
     * @return int
     */
    public function getCountOfMessagesInMainQueueOf($endpointFqcn);

    /**
     * @param string $endpointFqcn
     *
     * @return int
     */
    public function getCountOfMessagesInErrorQueueOf($endpointFqcn);
}
