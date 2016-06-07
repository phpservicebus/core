<?php

namespace spec\PSB\Core;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\OutgoingOptions;
use spec\PSB\Core\OutgoingOptionsSpec\SampleOptions;

/**
 * @mixin OutgoingOptions
 */
class OutgoingOptionsSpec extends ObjectBehavior
{
    function it_can_require_immediate_dispatch()
    {
        $this->beAnInstanceOf(SampleOptions::class);
        $this->isImmediateDispatchEnabled()->shouldBe(false);
        $this->requireImmediateDispatch();
        $this->isImmediateDispatchEnabled()->shouldBe(true);
    }

    function it_can_set_the_message_id()
    {
        $this->beAnInstanceOf(SampleOptions::class);

        $this->getMessageId()->shouldReturn(null);
        $this->setMessageId('id');
        $this->getMessageId()->shouldReturn('id');
    }

    function it_can_set_the_outgoing_headers()
    {
        $this->beAnInstanceOf(SampleOptions::class);

        $this->getOutgoingHeaders()->shouldReturn([]);
        $this->setOutgoingHeaders(['some' => 'header']);
        $this->getOutgoingHeaders()->shouldReturn(['some' => 'header']);
    }
}

namespace spec\PSB\Core\OutgoingOptionsSpec;

use PSB\Core\OutgoingOptions;

class SampleOptions extends OutgoingOptions
{
}
