<?php
namespace PSB\Core;


use PSB\Core\Pipeline\BusOperations;
use PSB\Core\Pipeline\PipelineRootStageContext;

class BusContext implements BusContextInterface
{
    /**
     * @var PipelineRootStageContext
     */
    private $rootContext;

    /**
     * @var BusOperations
     */
    private $busOperations;

    /**
     * @var OutgoingOptionsFactory
     */
    private $outgoingOptionsFactory;

    /**
     * @param PipelineRootStageContext $rootContext
     * @param BusOperations            $busOperations
     * @param OutgoingOptionsFactory   $outgoingOptionsFactory
     */
    public function __construct(
        PipelineRootStageContext $rootContext,
        BusOperations $busOperations,
        OutgoingOptionsFactory $outgoingOptionsFactory
    ) {
        $this->rootContext = $rootContext;
        $this->busOperations = $busOperations;
        $this->outgoingOptionsFactory = $outgoingOptionsFactory;
    }

    /**
     * @param object           $message
     * @param SendOptions|null $options
     */
    public function send($message, SendOptions $options = null)
    {
        $options = $options ?: $this->outgoingOptionsFactory->createSendOptions();

        $this->busOperations->send($message, $options, $this->rootContext);
    }

    /**
     * @param object           $message
     * @param SendOptions|null $options
     */
    public function sendLocal($message, SendOptions $options = null)
    {
        $options = $options ?: $this->outgoingOptionsFactory->createSendOptions();
        $options->routeToLocalEndpointInstance();

        $this->send($message, $options);
    }

    /**
     * @param object              $message
     * @param PublishOptions|null $options
     */
    public function publish($message, PublishOptions $options = null)
    {
        $options = $options ?: $this->outgoingOptionsFactory->createPublishOptions();

        $this->busOperations->publish($message, $options, $this->rootContext);
    }

    /**
     * @param string                $eventFqcn
     * @param SubscribeOptions|null $options
     */
    public function subscribe($eventFqcn, SubscribeOptions $options = null)
    {
        $options = $options ?: $this->outgoingOptionsFactory->createSubscribeOptions();

        $this->busOperations->subscribe($eventFqcn, $options, $this->rootContext);
    }

    /**
     * @param string                  $eventFqcn
     * @param UnsubscribeOptions|null $options
     */
    public function unsubscribe($eventFqcn, UnsubscribeOptions $options = null)
    {
        $options = $options ?: $this->outgoingOptionsFactory->createUnsubscribeOptions();

        $this->busOperations->unsubscribe($eventFqcn, $options, $this->rootContext);
    }
}
