<?php

namespace spec\PSB\Core\UuidGeneration\Comb;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\UuidGeneration\Comb\TimestampFirstCombGenerator;

/**
 * @mixin TimestampFirstCombGenerator
 */
class TimestampFirstCombGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\UuidGeneration\Comb\TimestampFirstCombGenerator');
    }

    function it_generates_a_properly_formatted_guid()
    {
        $this->generate()->shouldMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
    }

    function it_generates_monotone_guids() {
        $previous = $this->generate()->getWrappedObject();
        for($i = 0; $i < 100; $i++){
            usleep(10);
            $current = $this->generate();
            $current->shouldBeGreaterThan($previous);
            $previous = $current->getWrappedObject();
        }
    }

    public function getMatchers()
    {
        return [
            'beGreaterThan' => function ($subject, $key) {
                return $subject > $key;
            }
        ];
    }
}
