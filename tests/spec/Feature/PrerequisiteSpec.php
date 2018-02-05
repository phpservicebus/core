<?php

namespace spec\PSB\Core\Feature;

use PhpSpec\ObjectBehavior;

use PSB\Core\Feature\Prerequisite;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin Prerequisite
 */
class PrerequisiteSpec extends ObjectBehavior
{
    function it_contains_the_condition_set_during_construction(SimpleCallable $condition, $description)
    {
        $this->beConstructedWith($condition, $description);
        $this->getCondition()->shouldReturn($condition);
    }

    function it_contains_the_description_set_during_construction(SimpleCallable $condition, $description)
    {
        $this->beConstructedWith($condition, $description);
        $this->getDescription()->shouldReturn($description);
    }
}
