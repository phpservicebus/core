<?php

namespace spec\PSB\Core;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\DateTimeConverter;

/**
 * @mixin DateTimeConverter
 */
class DateTimeConverterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\DateTimeConverter');
    }

    function it_converts_from_datetime_to_wire_string()
    {
        $this->toWireFormattedString(new \DateTime('2016-03-11T03:45:40Z'))->shouldReturn('2016-03-11T03:45:40Z');
    }
}
