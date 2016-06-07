<?php
namespace PSB\Core\Exception;

/**
 * This exception should be thrown only when the bus has no chance of recovery. Such situations could include
 * infrastructure errors for transport or persistence, for example rabbitmq or sql server being inaccessible.
 *
 * It bypasses any retry mechanism or moving errors to the error queue and it effectively shuts down the endpoint.
 */
class CriticalErrorException extends \RuntimeException implements ExceptionInterface
{

}
