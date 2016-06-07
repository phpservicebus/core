<?php
namespace PSB\Core\Pipeline;


use PSB\Core\OutgoingOptions;
use PSB\Core\Pipeline\Incoming\IncomingContext;
use PSB\Core\PublishOptions;
use PSB\Core\ReplyOptions;
use PSB\Core\SendOptions;
use PSB\Core\SubscribeOptions;
use PSB\Core\UnsubscribeOptions;
use PSB\Core\UuidGeneration\UuidGeneratorInterface;

class BusOperations
{
    /**
     * @var PipelineFactory
     */
    private $pipelineFactory;

    /**
     * @var BusOperationsContextFactory
     */
    private $busOperationsContextFactory;

    /**
     * @var PipelineModifications
     */
    private $pipelineModifications;

    /**
     * @var UuidGeneratorInterface
     */
    private $uuidGenerator;

    /**
     * @param PipelineFactory             $pipelineFactory
     * @param BusOperationsContextFactory $busOperationsContextFactory
     * @param PipelineModifications       $pipelineModifications
     * @param UuidGeneratorInterface      $uuidGenerator
     */
    public function __construct(
        PipelineFactory $pipelineFactory,
        BusOperationsContextFactory $busOperationsContextFactory,
        PipelineModifications $pipelineModifications,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->pipelineFactory = $pipelineFactory;
        $this->busOperationsContextFactory = $busOperationsContextFactory;
        $this->pipelineModifications = $pipelineModifications;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @param object               $message
     * @param SendOptions          $options
     * @param PipelineStageContext $parentContext
     */
    public function send($message, SendOptions $options, PipelineStageContext $parentContext)
    {
        $this->ensureMessageId($options);
        $sendContext = $this->busOperationsContextFactory->createSendContext($message, $options, $parentContext);
        $pipeline = $this->pipelineFactory->createStartingWith(get_class($sendContext), $this->pipelineModifications);

        $pipeline->invoke($sendContext);
    }

    /**
     * @param object               $message
     * @param SendOptions          $options
     * @param PipelineStageContext $parentContext
     */
    public function sendLocal($message, SendOptions $options, PipelineStageContext $parentContext)
    {
        $options->routeToLocalEndpointInstance();

        $this->send($message, $options, $parentContext);
    }

    /**
     * @param object               $message
     * @param PublishOptions       $options
     * @param PipelineStageContext $parentContext
     */
    public function publish($message, PublishOptions $options, PipelineStageContext $parentContext)
    {
        $this->ensureMessageId($options);
        $publishContext = $this->busOperationsContextFactory->createPublishContext($message, $options, $parentContext);
        $pipeline = $this->pipelineFactory->createStartingWith(
            get_class($publishContext),
            $this->pipelineModifications
        );

        $pipeline->invoke($publishContext);
    }

    /**
     * @param object          $message
     * @param ReplyOptions    $options
     * @param IncomingContext $parentContext
     */
    public function reply($message, ReplyOptions $options, IncomingContext $parentContext)
    {
        $this->ensureMessageId($options);
        $publishContext = $this->busOperationsContextFactory->createReplyContext($message, $options, $parentContext);
        $pipeline = $this->pipelineFactory->createStartingWith(
            get_class($publishContext),
            $this->pipelineModifications
        );

        $pipeline->invoke($publishContext);
    }

    /**
     * @param string               $eventFqcn
     * @param SubscribeOptions     $options
     * @param PipelineStageContext $parentContext
     */
    public function subscribe($eventFqcn, SubscribeOptions $options, PipelineStageContext $parentContext)
    {
        $subscribeContext = $this->busOperationsContextFactory->createSubscribeContext(
            $eventFqcn,
            $options,
            $parentContext
        );
        $pipeline = $this->pipelineFactory->createStartingWith(
            get_class($subscribeContext),
            $this->pipelineModifications
        );

        $pipeline->invoke($subscribeContext);
    }

    /**
     * @param string               $eventFqcn
     * @param UnsubscribeOptions   $options
     * @param PipelineStageContext $parentContext
     */
    public function unsubscribe($eventFqcn, UnsubscribeOptions $options, PipelineStageContext $parentContext)
    {
        $unsubscribeContext = $this->busOperationsContextFactory->createUnsubscribeContext(
            $eventFqcn,
            $options,
            $parentContext
        );
        $pipeline = $this->pipelineFactory->createStartingWith(
            get_class($unsubscribeContext),
            $this->pipelineModifications
        );

        $pipeline->invoke($unsubscribeContext);
    }

    /**
     * @param OutgoingOptions $options
     */
    private function ensureMessageId(OutgoingOptions $options)
    {
        if (!$options->getMessageId()) {
            $options->setMessageId($this->uuidGenerator->generate());
        }
    }
}
