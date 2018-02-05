<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;

use PSB\Core\Transport\OutgoingPhysicalMessage;

/**
 * @mixin OutgoingPhysicalMessage
 */
class OutgoingPhysicalMessageSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('id', [], 'body');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\OutgoingPhysicalMessage');
    }

    function it_contains_the_id_set_at_construction()
    {
        $this->getMessageId()->shouldReturn('id');
    }

    function it_contains_the_headers_set_at_construction()
    {
        $this->getHeaders()->shouldReturn([]);
    }

    function it_contains_the_body_set_at_construction()
    {
        $this->getBody()->shouldReturn('body');
    }

    function it_can_set_a_header()
    {
        $this->setHeader('name', 'value');

        $this->getHeaders()->shouldReturn(['name' => 'value']);
    }

    function it_allows_the_headers_to_be_replaced()
    {
        $this->replaceHeaders(['what' => 'ever']);

        $this->getHeaders()->shouldReturn(['what' => 'ever']);
    }

    function it_allows_the_body_to_be_replaced()
    {
        $this->replaceBody('newbody');

        $this->getBody()->shouldReturn('newbody');
    }

    function it_throws_on_null_body_on_construction()
    {
        $this->beConstructedWith('id', [], null);

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_on_null_headers_on_construction()
    {
        $this->beConstructedWith('id', null, 'body');

        $this->shouldThrow()->duringInstantiation();
    }

    function it_throws_on_empty_id_on_construction()
    {
        $this->beConstructedWith(0, [], 'body');

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_on_null_body_on_replacement()
    {
        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringReplaceBody(null);
    }
}
