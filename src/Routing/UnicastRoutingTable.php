<?php
namespace PSB\Core\Routing;


class UnicastRoutingTable
{
    /**
     * @var array
     */
    private $staticRules = [];

    /**
     * @param string $messageFqcn
     * @param string $endpointName
     */
    public function routeToEndpoint($messageFqcn, $endpointName)
    {
        if (!isset($this->staticRules[$messageFqcn])) {
            $this->staticRules[$messageFqcn] = [];
        }

        if (in_array($endpointName, $this->staticRules[$messageFqcn])) {
            return;
        }

        $this->staticRules[$messageFqcn][] = $endpointName;
    }

    /**
     * @param string[] $messageTypes
     *
     * @return string[]
     */
    public function getEndpointNamesFor(array $messageTypes)
    {
        $endpoints = [];
        foreach ($messageTypes as $messageType) {
            if (isset($this->staticRules[$messageType])) {
                $endpoints = array_merge($endpoints, $this->staticRules[$messageType]);
            }
        }

        return array_unique($endpoints);
    }
}
