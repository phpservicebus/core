<?php
namespace PSB\Core\Transport\RabbitMq;


use PSB\Core\Exception\InvalidArgumentException;
use PSB\Core\HeaderTypeEnum;
use PSB\Core\Transport\OutgoingPhysicalMessage;

class MessageConverter
{
    public function retrieveMessageId(\AMQPEnvelope $envelope)
    {
        $messageId = $envelope->getMessageId();
        if ($messageId === '' || $messageId === null) {
            throw new InvalidArgumentException(
                "A non empty message-id attribute is required when running PHPServiceBus on top of RabbitMq."
            );
        }

        return $messageId;
    }

    public function retrieveHeaders(\AMQPEnvelope $envelope)
    {
        $headers = $envelope->getHeaders();

        if ($envelope->getReplyTo() !== '' && !isset($headers[HeaderTypeEnum::REPLY_TO_ADDRESS])) {
            $headers[HeaderTypeEnum::REPLY_TO_ADDRESS] = $envelope->getReplyTo();
        }

        if ($envelope->getCorrelationId() !== '') {
            $headers[HeaderTypeEnum::CORRELATION_ID] = $envelope->getCorrelationId();
        }

        if ($envelope->getType() !== '' && !isset($headers[HeaderTypeEnum::ENCLOSED_CLASS])) {
            $headers[HeaderTypeEnum::ENCLOSED_CLASS] = $envelope->getType();
        }

        return $headers;
    }

    /**
     * @param OutgoingPhysicalMessage $message
     *
     * @return array
     */
    public function composeRabbitMqAttributes(OutgoingPhysicalMessage $message)
    {
        $headers = $message->getHeaders();

        $attributes = [];
        $attributes['message_id'] = $message->getMessageId();
        $attributes['headers'] = $headers;

        if (isset($headers[HeaderTypeEnum::CORRELATION_ID])) {
            $attributes['correlation_id'] = $headers[HeaderTypeEnum::CORRELATION_ID];
        }

        if (isset($headers[HeaderTypeEnum::CONTENT_TYPE])) {
            $attributes['content_type'] = $headers[HeaderTypeEnum::CONTENT_TYPE];
        } else {
            $attributes['content_type'] = 'application/octet-stream';
        }

        if (isset($headers[HeaderTypeEnum::REPLY_TO_ADDRESS])) {
            $attributes['reply_to'] = $headers[HeaderTypeEnum::REPLY_TO_ADDRESS];
        }

        if (isset($headers[HeaderTypeEnum::ENCLOSED_CLASS])) {
            $attributes['type'] = $headers[HeaderTypeEnum::ENCLOSED_CLASS];
        }

        return $attributes;
    }
}
