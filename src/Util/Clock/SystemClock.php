<?php
namespace PSB\Core\Util\Clock;


/**
 * @codeCoverageIgnore
 */
class SystemClock implements ClockInterface
{
    /**
     * @param \DateTimeZone|null $timezone
     *
     * @return \DateTime
     */
    public function now(\DateTimeZone $timezone = null)
    {
        return new \DateTime('now', $timezone ?: new \DateTimeZone('UTC'));
    }
}
