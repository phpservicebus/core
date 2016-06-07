<?php
namespace PSB\Core\Util;


use PSB\Core\Exception\InvalidArgumentException;

class Guard
{
    public static function againstNull($name, $value)
    {
        if ($value === null) {
            throw new InvalidArgumentException(ucfirst($name) . ' cannot be null.');
        }
    }

    public static function againstNullAndEmpty($name, $value)
    {
        if ($value === null || empty($value)) {
            throw new InvalidArgumentException(ucfirst($name) . ' cannot be null.');
        }
    }

    public static function againstNullAndNonInt($name, $value)
    {
        if ($value === null || !is_int($value)) {
            throw new InvalidArgumentException(ucfirst($name) . ' must be a non null integer.');
        }
    }

    public static function againstNonObject($name, $value)
    {
        if (!is_object($value)) {
            throw new InvalidArgumentException(ucfirst($name) . ' must be an object.');
        }
    }
}
