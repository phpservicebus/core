<?php
namespace PSB\Core\Util\Clock;


interface ClockInterface
{
    /**
     * @param \DateTimeZone|null $timezone
     *
     * @return \DateTime
     */
    public function now(\DateTimeZone $timezone = null);
}
