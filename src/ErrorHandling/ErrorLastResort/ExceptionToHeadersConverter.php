<?php
namespace PSB\Core\ErrorHandling\ErrorLastResort;


use PSB\Core\DateTimeConverter;
use PSB\Core\Util\Clock\ClockInterface;

class ExceptionToHeadersConverter
{
    /**
     * @var DateTimeConverter
     */
    private $timeConverter;

    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     * @param ClockInterface    $clock
     * @param DateTimeConverter $timeConverter
     */
    public function __construct(ClockInterface $clock, DateTimeConverter $timeConverter)
    {
        $this->clock = $clock;
        $this->timeConverter = $timeConverter;
    }

    /**
     * @param \Throwable $t
     * @param string     $localAddress
     *
     * @return array
     */
    public function convert(\Throwable $t, $localAddress)
    {
        $headers = [
            ErrorLastResortHeaderTypeEnum::TIME_OF_FAILURE => $this->timeConverter->toWireFormattedString(
                $this->clock->now(new \DateTimeZone('UTC'))
            ),
            ErrorLastResortHeaderTypeEnum::FAILED_QUEUE => $localAddress,
            ErrorLastResortHeaderTypeEnum::EXCEPTION_TYPE => get_class($t),
            ErrorLastResortHeaderTypeEnum::EXCEPTION_MESSAGE => $t->getMessage(),
            ErrorLastResortHeaderTypeEnum::EXCEPTION_FILE => $t->getFile() . ':' . $t->getLine(),
            ErrorLastResortHeaderTypeEnum::EXCEPTION_TRACE => $t->getTraceAsString()
        ];

        $pe = $t->getPrevious();
        if ($pe) {
            $headers[ErrorLastResortHeaderTypeEnum::PREV_EXCEPTION_TYPE] = get_class($pe);
            $headers[ErrorLastResortHeaderTypeEnum::PREV_EXCEPTION_MESSAGE] = $pe->getMessage();
            $headers[ErrorLastResortHeaderTypeEnum::PREV_EXCEPTION_FILE] = $pe->getFile() . ':' . $pe->getLine();
            $headers[ErrorLastResortHeaderTypeEnum::PREV_EXCEPTION_TRACE] = $pe->getTraceAsString();
        }

        return $headers;
    }
}
