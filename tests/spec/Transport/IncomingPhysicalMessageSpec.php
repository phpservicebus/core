<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;

use PSB\Core\HeaderTypeEnum;
use PSB\Core\Transport\IncomingPhysicalMessage;

/**
 * @mixin IncomingPhysicalMessage
 */
class IncomingPhysicalMessageSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('id', ['some' => 'header'], 'body');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\IncomingPhysicalMessage');
    }

    function it_contains_the_id_set_at_construction()
    {
        $this->getMessageId()->shouldReturn('id');
    }

    function it_contains_the_headers_set_at_construction()
    {
        $this->getHeaders()->shouldReturn(['some' => 'header']);
    }

    function it_contains_the_body_set_at_construction()
    {
        $this->getBody()->shouldReturn('body');
    }

    function it_gets_the_reply_to_address_if_set_as_a_header()
    {
        $this->beConstructedWith('id', [HeaderTypeEnum::REPLY_TO_ADDRESS => 'irrelevant'], 'body');
        $this->getReplyToAddress()->shouldReturn('irrelevant');
    }

    function it_can_set_a_header()
    {
        $this->setHeader('some other', 'header');

        $this->getHeaders()->shouldReturn(['some' => 'header', 'some other' => 'header']);
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

    function it_can_revert_to_the_original_body_if_the_body_was_changed()
    {
        $this->replaceBody('newbody');
        $this->revertToOriginalBodyIfNeeded();
        $this->getBody()->shouldReturn('body');
    }

    function it_does_nothing_to_revert_to_the_original_body_if_the_body_was_not_changed()
    {
        $this->revertToOriginalBodyIfNeeded();
        $this->getBody()->shouldReturn('body');
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
