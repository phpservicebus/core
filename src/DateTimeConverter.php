<?php
namespace PSB\Core;

/**
 * The assumption made by this class is that all times are provided by a Clock which deals only in UTC
 */
class DateTimeConverter
{
    private $format = 'Y-m-d\TH:i:s\Z';

    /**
     * @param \DateTime $date
     *
     * @return string
     */
    public function toWireFormattedString(\DateTime $date)
    {
        return $date->format($this->format);
    }
}
