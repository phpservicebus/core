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
     * @param \Exception $e
     * @param string     $localAddress
     *
     * @return array
     */
    public function convert(\Exception $e, $localAddress)
    {
        $headers = [
            ErrorLastResortHeaderTypeEnum::TIME_OF_FAILURE => $this->timeConverter->toWireFormattedString(
                $this->clock->now(new \DateTimeZone('UTC'))
            ),
            ErrorLastResortHeaderTypeEnum::FAILED_QUEUE => $localAddress,
            ErrorLastResortHeaderTypeEnum::EXCEPTION_TYPE => get_class($e),
            ErrorLastResortHeaderTypeEnum::EXCEPTION_MESSAGE => $e->getMessage(),
            ErrorLastResortHeaderTypeEnum::EXCEPTION_FILE => $e->getFile() . ':' . $e->getLine(),
            ErrorLastResortHeaderTypeEnum::EXCEPTION_TRACE => $e->getTraceAsString()
        ];

        $pe = $e->getPrevious();
        if ($pe) {
            $headers[ErrorLastResortHeaderTypeEnum::PREV_EXCEPTION_TYPE] = get_class($pe);
            $headers[ErrorLastResortHeaderTypeEnum::PREV_EXCEPTION_MESSAGE] = $pe->getMessage();
            $headers[ErrorLastResortHeaderTypeEnum::PREV_EXCEPTION_FILE] = $pe->getFile() . ':' . $pe->getLine();
            $headers[ErrorLastResortHeaderTypeEnum::PREV_EXCEPTION_TRACE] = $pe->getTraceAsString();
        }

        return $headers;
    }
}
