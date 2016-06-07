<?php
namespace PSB\Core\Pipeline\Outgoing;


use PSB\Core\DateTimeConverter;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\Pipeline\Outgoing\StageContext\DispatchContext;
use PSB\Core\Pipeline\Outgoing\StageContext\OutgoingPhysicalMessageContext;
use PSB\Core\Pipeline\StageConnectorInterface;
use PSB\Core\Transport\TransportOperation;
use PSB\Core\Util\Clock\ClockInterface;

class OutgoingPhysicalToDispatchConnector implements StageConnectorInterface
{
    /**
     * @var OutgoingContextFactory
     */
    private $contextFactory;

    /**
     * @var DateTimeConverter
     */
    private $dateTimeConverter;

    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     *
     * @param OutgoingContextFactory $contextFactory
     * @param DateTimeConverter      $dateTimeConverter
     * @param ClockInterface         $clock
     */
    public function __construct(
        OutgoingContextFactory $contextFactory,
        DateTimeConverter $dateTimeConverter,
        ClockInterface $clock
    ) {
        $this->contextFactory = $contextFactory;
        $this->dateTimeConverter = $dateTimeConverter;
        $this->clock = $clock;
    }

    /**
     * @param OutgoingPhysicalMessageContext $context
     * @param callable                       $next
     */
    public function invoke($context, callable $next)
    {
        $context->setHeader(HeaderTypeEnum::MESSAGE_ID, $context->getMessageId());
        $context->setHeader(
            HeaderTypeEnum::TIME_SENT,
            $this->dateTimeConverter->toWireFormattedString($this->clock->now())
        );

        $transportOperations = [];
        foreach ($context->getAddressTags() as $addressTag) {
            $transportOperations[] = new TransportOperation(clone $context->getMessage(), $addressTag);
        }

        $pendingOperations = $context->getPendingTransportOperations();

        if (!$context->isImmediateDispatchEnabled() && $pendingOperations) {
            $pendingOperations->addAll($transportOperations);
            return;
        }

        $next($this->contextFactory->createDispatchContext($transportOperations, $context));
    }

    /**
     * @return string
     */
    public static function getStageContextClass()
    {
        return OutgoingPhysicalMessageContext::class;
    }

    /**
     * @return string
     */
    public static function getNextStageContextClass()
    {
        return DispatchContext::class;
    }
}
