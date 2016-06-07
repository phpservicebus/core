<?php
namespace PSB\Core\Exception;


class MessageDeserializationException extends \UnexpectedValueException implements ExceptionInterface
{
    public function __construct($physicalMessageId, \Exception $previous = null)
    {
        parent::__construct(
            "An error occurred while attempting to extract a logical message from the physical message with ID '$physicalMessageId'",
            0,
            $previous
        );
    }
}
