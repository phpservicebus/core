<?php
namespace acceptancesupport\PSB\Core\Scenario;


class EndpointBehavior
{
    /**
     * @var EndpointConfiguratorProxy
     */
    private $endpointBuilder;

    /**
     * @var callable
     */
    private $onPrepared;

    /**
     * @var callable
     */
    private $onStarted;

    /**
     * @var bool
     */
    private $isRunInBackground;

    /**
     * @param EndpointConfiguratorProxy $endpointConfigProxy
     * @param callable|null             $onPrepared
     * @param callable|null             $onStarted
     * @param bool                      $isRunInBackground
     */
    public function __construct(
        EndpointConfiguratorProxy $endpointConfigProxy,
        callable $onPrepared = null,
        callable $onStarted = null,
        $isRunInBackground = true
    ) {
        $this->endpointBuilder = $endpointConfigProxy;
        $this->onPrepared = $onPrepared;
        $this->onStarted = $onStarted;
        $this->isRunInBackground = $isRunInBackground;
    }

    /**
     * @return EndpointConfiguratorProxy
     */
    public function getEndpointConfigProxy()
    {
        return $this->endpointBuilder;
    }

    /**
     * @return callable
     */
    public function getOnPrepared()
    {
        return $this->onPrepared;
    }

    /**
     * @return callable
     */
    public function getOnStarted()
    {
        return $this->onStarted;
    }

    /**
     * @return boolean
     */
    public function isRunInBackground()
    {
        return $this->isRunInBackground;
    }
}
