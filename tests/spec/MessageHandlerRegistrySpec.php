<?php

namespace spec\PSB\Core;

use PhpSpec\ObjectBehavior;

use PSB\Core\MessageHandlerRegistry;

/**
 * @mixin MessageHandlerRegistry
 */
class MessageHandlerRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\MessageHandlerRegistry');
    }

    function it_provides_handler_ids_for_requested_message_fqcns()
    {
        $this->registerEventHandler('fqcn3', 'handler3');
        $this->registerCommandHandler('fqcn1', 'handler1');
        $this->registerCommandHandler('fqcn2', 'handler2');

        $this->getHandlerIdsFor(['fqcn2', 'fqcn3'])->shouldReturn(['handler3', 'handler2']);
    }

    function it_provides_class_fqcns_for_registered_event_handlers()
    {
        $this->registerEventHandler('fqcn1', 'handler1');
        $this->registerEventHandler('fqcn2', 'handler1');
        $this->registerEventHandler('fqcn3', 'handler1');

        $this->getEventFqcns()->shouldReturn(['fqcn1', 'fqcn2', 'fqcn3']);
    }

    function it_provides_class_fqcns_for_registered_command_handlers()
    {
        $this->registerCommandHandler('fqcn1', 'handler1');
        $this->registerCommandHandler('fqcn2', 'handler1');
        $this->registerCommandHandler('fqcn3', 'handler1');

        $this->getCommandFqcns()->shouldReturn(['fqcn1', 'fqcn2', 'fqcn3']);
    }

    function it_provides_class_fqcns_for_all_registered_message_handlers()
    {
        $this->registerEventHandler('fqcn3', 'handler3');
        $this->registerCommandHandler('fqcn1', 'handler1');
        $this->registerCommandHandler('fqcn2', 'handler2');

        $this->getMessageFqcns()->shouldReturn(['fqcn3', 'fqcn1', 'fqcn2']);
    }

    function it_does_not_register_the_same_message_handler_multiple_times(){
        $this->registerEventHandler('fqcn3', 'handler3');
        $this->registerEventHandler('fqcn3', 'handler3');
        $this->registerCommandHandler('fqcn1', 'handler1');
        $this->registerCommandHandler('fqcn1', 'handler1');
        $this->getMessageFqcns()->shouldReturn(['fqcn3', 'fqcn1']);
    }
}
