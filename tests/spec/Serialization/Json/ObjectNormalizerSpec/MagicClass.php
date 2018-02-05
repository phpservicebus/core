<?php

namespace spec\PSB\Core\Serialization\Json\ObjectNormalizerSpec;


class MagicClass
{
    public $show = true;

    public $hide = true;

    public $woke = false;

    public function __sleep()
    {
        return ['show'];
    }

    public function __wakeup()
    {
        $this->woke = true;
    }
}
