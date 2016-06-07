<?php

namespace spec\PSB\Core\Pipeline\Outgoing;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Pipeline\PipelineStageContext;
use spec\PSB\Core\Pipeline\Outgoing\OutgoingContextSpec\SampleOutgoingContext;

/**
 * @mixin SampleOutgoingContext
 */
class OutgoingContextSpec extends ObjectBehavior
{
    /**
     * @var
     */
    private $messageId = 'irrelevant';
    /**
     * @var array
     */
    private $headers = ['irrele' => 'vant'];
    /**
     * @var PipelineStageContext
     */
    private $parentContext;

    function let(PipelineStageContext $parentContext)
    {
        $this->parentContext = $parentContext;
        $this->beAnInstanceOf(SampleOutgoingContext::class);
        $this->beConstructedWith($this->messageId, $this->headers, $parentContext);
    }

    function it_contains_the_message_id_set_at_construction()
    {
        $this->getMessageId()->shouldReturn($this->messageId);
    }

    function it_contains_the_headers_set_at_construction()
    {
        $this->getHeaders()->shouldReturn($this->headers);
    }

    function it_sets_a_header()
    {
        $this->setHeader('some', 'value');
        $this->getHeaders()->shouldReturn(array_merge($this->headers, ['some' => 'value']));
    }

    function it_replaces_headers()
    {
        $this->replaceHeaders(['some' => 'value']);
        $this->getHeaders()->shouldReturn(['some' => 'value']);
    }
}

namespace spec\PSB\Core\Pipeline\Outgoing\OutgoingContextSpec;

use PSB\Core\Pipeline\Outgoing\OutgoingContext;

class SampleOutgoingContext extends OutgoingContext
{
}
