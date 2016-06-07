<?php

namespace spec\PSB\Core\Transport\RabbitMq;

use AMQPConnection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Transport\RabbitMq\BrokerModel;

/**
 * @mixin BrokerModel
 *
 * @TODO This needs to be tested with an actual amqp connection otherwise is pointless/impossible
 */
class BrokerModelSpec extends ObjectBehavior
{
    /**
     * @var AMQPConnection
     */
    private $connectionMock;

    function let(AMQPConnection $connection)
    {
        $this->connectionMock = $connection;
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\RabbitMq\BrokerModel');
    }

}
