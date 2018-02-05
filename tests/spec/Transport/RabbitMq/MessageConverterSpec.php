<?php

namespace spec\PSB\Core\Transport\RabbitMq;

use PhpSpec\ObjectBehavior;

use PSB\Core\HeaderTypeEnum;
use PSB\Core\Transport\OutgoingPhysicalMessage;
use PSB\Core\Transport\RabbitMq\MessageConverter;

/**
 * @mixin MessageConverter
 */
class MessageConverterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\RabbitMq\MessageConverter');
    }

    function it_retrieves_the_message_id_from_the_envelope_if_it_exists(\AMQPEnvelope $envelope)
    {
        $envelope->getMessageId()->willReturn('id');

        $this->retrieveMessageId($envelope)->shouldReturn('id');
    }

    function it_throws_when_retrieving_the_message_id_if_envelope_has_no_message_id(\AMQPEnvelope $envelope)
    {
        $envelope->getMessageId()->willReturn('');

        $this->shouldThrow('PSB\Core\Exception\InvalidArgumentException')->duringRetrieveMessageId($envelope);
    }

    function it_retrieves_the_base_headers_from_the_envelope_if_no_defaults_are_available(\AMQPEnvelope $envelope)
    {
        $envelope->getHeaders()->willReturn(['key' => 'value']);
        $envelope->getReplyTo()->willReturn('');
        $envelope->getCorrelationId()->willReturn('');
        $envelope->getType()->willReturn('');

        $this->retrieveHeaders($envelope)->shouldReturn(['key' => 'value']);
    }

    function it_retrieves_the_headers_including_defaults_from_the_envelope_if_defaults_are_available(
        \AMQPEnvelope $envelope
    ) {
        $envelope->getHeaders()->willReturn(['key' => 'value']);
        $envelope->getReplyTo()->willReturn('replyto');
        $envelope->getCorrelationId()->willReturn('correlationid');
        $envelope->getType()->willReturn('type');

        $this->retrieveHeaders($envelope)->shouldReturn(
            [
                'key' => 'value',
                HeaderTypeEnum::REPLY_TO_ADDRESS => 'replyto',
                HeaderTypeEnum::CORRELATION_ID => 'correlationid',
                HeaderTypeEnum::ENCLOSED_CLASS => 'type'
            ]
        );
    }

    function it_retrieves_the_headers_without_defaults_if_they_already_exist_even_if_defaults_are_available(
        \AMQPEnvelope $envelope
    ) {
        $envelope->getHeaders()->willReturn(
            [
                'key' => 'value',
                HeaderTypeEnum::REPLY_TO_ADDRESS => 'replyto1',
                HeaderTypeEnum::CORRELATION_ID => 'correlationid1',
                HeaderTypeEnum::ENCLOSED_CLASS => 'type1'
            ]
        );
        $envelope->getReplyTo()->willReturn('replyto2');
        $envelope->getCorrelationId()->willReturn('correlationid2');
        $envelope->getType()->willReturn('type2');

        $this->retrieveHeaders($envelope)->shouldReturn(
            [
                'key' => 'value',
                HeaderTypeEnum::REPLY_TO_ADDRESS => 'replyto1',
                HeaderTypeEnum::CORRELATION_ID => 'correlationid2',
                HeaderTypeEnum::ENCLOSED_CLASS => 'type1'
            ]
        );
    }

    function it_composes_rabbitmq_attributes_from_message_using_defaults(OutgoingPhysicalMessage $message)
    {
        $message->getHeaders()->willReturn(['some' => 'header']);
        $message->getMessageId()->willReturn('someid');
        $this->composeRabbitMqAttributes($message)->shouldReturn(
            ['message_id' => 'someid', 'headers' => ['some' => 'header'], 'content_type' => 'application/octet-stream']
        );
    }

    function it_composes_rabbitmq_attributes_from_message_using_correlation_id_override(OutgoingPhysicalMessage $message
    ) {
        $headers = [
            'some' => 'header',
            HeaderTypeEnum::CORRELATION_ID => 'somecorrelationid',
        ];

        $message->getHeaders()->willReturn($headers);
        $message->getMessageId()->willReturn('someid');

        $this->composeRabbitMqAttributes($message)->shouldReturn(
            [
                'message_id' => 'someid',
                'headers' => $headers,
                'correlation_id' => 'somecorrelationid',
                'content_type' => 'application/octet-stream'
            ]
        );
    }

    function it_composes_rabbitmq_attributes_from_message_using_content_type_override(OutgoingPhysicalMessage $message)
    {
        $headers = [
            'some' => 'header',
            HeaderTypeEnum::CONTENT_TYPE => 'somecontent',
        ];

        $message->getHeaders()->willReturn($headers);
        $message->getMessageId()->willReturn('someid');

        $this->composeRabbitMqAttributes($message)->shouldReturn(
            [
                'message_id' => 'someid',
                'headers' => $headers,
                'content_type' => 'somecontent'
            ]
        );
    }

    function it_composes_rabbitmq_attributes_from_message_using_reply_to_address_override(
        OutgoingPhysicalMessage $message
    ) {
        $headers = [
            'some' => 'header',
            HeaderTypeEnum::REPLY_TO_ADDRESS => 'someaddress',
        ];

        $message->getHeaders()->willReturn($headers);
        $message->getMessageId()->willReturn('someid');

        $this->composeRabbitMqAttributes($message)->shouldReturn(
            [
                'message_id' => 'someid',
                'headers' => $headers,
                'content_type' => 'application/octet-stream',
                'reply_to' => 'someaddress'
            ]
        );
    }

    function it_composes_rabbitmq_attributes_from_message_using_enclosed_class_override(
        OutgoingPhysicalMessage $message
    ) {
        $headers = [
            'some' => 'header',
            HeaderTypeEnum::ENCLOSED_CLASS => 'someaclass',
        ];

        $message->getHeaders()->willReturn($headers);
        $message->getMessageId()->willReturn('someid');

        $this->composeRabbitMqAttributes($message)->shouldReturn(
            [
                'message_id' => 'someid',
                'headers' => $headers,
                'content_type' => 'application/octet-stream',
                'type' => 'someaclass'
            ]
        );
    }
}
