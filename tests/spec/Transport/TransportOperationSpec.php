<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Routing\AddressTagInterface;
use PSB\Core\Transport\OutgoingPhysicalMessage;
use PSB\Core\Transport\TransportOperation;

/**
 * @mixin TransportOperation
 */
class TransportOperationSpec extends ObjectBehavior
{
    private $messageMock;
    private $addressTagMock;

    function let(OutgoingPhysicalMessage $message, AddressTagInterface $addressTag)
    {
        $this->messageMock = $message;
        $this->addressTagMock = $addressTag;
        $this->beConstructedWith($message, $addressTag);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\TransportOperation');
    }

    function it_contains_the_message_set_at_construction()
    {
        $this->getMessage()->shouldReturn($this->messageMock);
    }

    function it_contains_the_address_tag_set_at_construction()
    {
        $this->getAddressTag()->shouldReturn($this->addressTagMock);
    }
}
