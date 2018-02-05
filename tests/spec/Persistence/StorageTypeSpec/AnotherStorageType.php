<?php

namespace spec\PSB\Core\Persistence\StorageTypeSpec;


use PSB\Core\Persistence\StorageType;

class AnotherStorageType extends StorageType
{
    const WHATEVER = 'Whatever';
    static protected $constants = ['WHATEVER' => self::WHATEVER];

    static public function WHATEVER()
    {
        return new self(static::WHATEVER);
    }
}
