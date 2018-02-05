<?php

namespace spec\PSB\Core\ErrorHandling\ErrorLastResort;

use PhpSpec\ObjectBehavior;

use PSB\Core\DateTimeConverter;
use PSB\Core\ErrorHandling\ErrorLastResort\ErrorLastResortHeaderTypeEnum;
use PSB\Core\ErrorHandling\ErrorLastResort\ExceptionToHeadersConverter;
use PSB\Core\Util\Clock\ClockInterface;

/**
 * @mixin ExceptionToHeadersConverter
 */
class ExceptionToHeadersConverterSpec extends ObjectBehavior
{
    /**
     * @var ClockInterface
     */
    private $clockMock;
    /**
     * @var DateTimeConverter
     */
    private $timeConverterMock;

    function let(ClockInterface $clock, DateTimeConverter $timeConverter)
    {
        $this->clockMock = $clock;
        $this->timeConverterMock = $timeConverter;
        $this->beConstructedWith($clock, $timeConverter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\ErrorHandling\ErrorLastResort\ExceptionToHeadersConverter');
    }

    function it_converts_simple_exception_to_headers()
    {
        $message = 'irrelevant';
        $localAddress = 'irrelevant address';
        $failureTime = '2016-03-08T09:36:09Z';
        $failureDateTime = new \DateTime($failureTime, new \DateTimeZone('UTC'));
        try {
            throw new \Exception($message);
        } catch (\Exception $e) {
            $this->clockMock->now(new \DateTimeZone('UTC'))->willReturn(
                $failureDateTime
            );
            $this->timeConverterMock->toWireFormattedString($failureDateTime)->willReturn($failureTime);

            $return = $this->convert($e, $localAddress);

            $return->shouldHaveKeyWithValue(ErrorLastResortHeaderTypeEnum::TIME_OF_FAILURE, $failureTime);
            $return->shouldHaveKeyWithValue(ErrorLastResortHeaderTypeEnum::FAILED_QUEUE, $localAddress);
            $return->shouldHaveKeyWithValue(ErrorLastResortHeaderTypeEnum::EXCEPTION_TYPE, 'Exception');
            $return->shouldHaveKeyWithValue(ErrorLastResortHeaderTypeEnum::EXCEPTION_MESSAGE, $message);
            $return->shouldHaveKey(ErrorLastResortHeaderTypeEnum::EXCEPTION_FILE);
            $return->shouldHaveKey(ErrorLastResortHeaderTypeEnum::EXCEPTION_TRACE);

            $return->shouldNotHaveKey(ErrorLastResortHeaderTypeEnum::PREV_EXCEPTION_TYPE);
        }
    }

    function it_converts_exception_wrapping_another_to_headers()
    {
        $firstMessage = 'irrelevant1';
        $secondMessage = 'irrelevant2';
        $localAddress = 'irrelevant address';
        $failureTime = '2016-03-08T09:36:09Z';
        $failureDateTime = new \DateTime($failureTime, new \DateTimeZone('UTC'));
        try {
            try {
                throw new \Exception($firstMessage);
            } catch (\Exception $e) {
                throw new \Exception($secondMessage, 0, $e);
            }
        } catch (\Exception $e) {
            $this->clockMock->now(new \DateTimeZone('UTC'))->willReturn(
                $failureDateTime
            );
            $this->timeConverterMock->toWireFormattedString($failureDateTime)->willReturn($failureTime);

            $return = $this->convert($e, $localAddress);

            $return->shouldHaveKeyWithValue(ErrorLastResortHeaderTypeEnum::TIME_OF_FAILURE, $failureTime);
            $return->shouldHaveKeyWithValue(ErrorLastResortHeaderTypeEnum::FAILED_QUEUE, $localAddress);
            $return->shouldHaveKeyWithValue(ErrorLastResortHeaderTypeEnum::EXCEPTION_TYPE, 'Exception');
            $return->shouldHaveKeyWithValue(ErrorLastResortHeaderTypeEnum::EXCEPTION_MESSAGE, $secondMessage);
            $return->shouldHaveKey(ErrorLastResortHeaderTypeEnum::EXCEPTION_FILE);
            $return->shouldHaveKey(ErrorLastResortHeaderTypeEnum::EXCEPTION_TRACE);

            $return->shouldHaveKeyWithValue(ErrorLastResortHeaderTypeEnum::PREV_EXCEPTION_TYPE, 'Exception');
            $return->shouldHaveKeyWithValue(ErrorLastResortHeaderTypeEnum::PREV_EXCEPTION_MESSAGE, $firstMessage);
            $return->shouldHaveKey(ErrorLastResortHeaderTypeEnum::PREV_EXCEPTION_FILE);
            $return->shouldHaveKey(ErrorLastResortHeaderTypeEnum::PREV_EXCEPTION_TRACE);
        }
    }
}
