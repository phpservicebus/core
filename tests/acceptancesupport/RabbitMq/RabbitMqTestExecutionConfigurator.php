<?php
namespace acceptancesupport\PSB\Core\RabbitMq;


use acceptancesupport\PSB\Core\Scenario\EndpointQueuesInformationProviderInterface;
use acceptancesupport\PSB\Core\Scenario\EndpointTestExecutionConfiguratorInterface;
use Markup\RabbitMq\ManagementApi\Api\Exchange;
use Markup\RabbitMq\ManagementApi\Api\Queue;
use PSB\Core\EndpointConfigurator;
use PSB\Core\Transport\RabbitMq\Config\RabbitMqTransportConfigurator;
use PSB\Core\Transport\RabbitMq\Config\RabbitMqTransportDefinition;
use PSB\Core\Transport\RabbitMq\RoutingTopology;
use Markup\RabbitMq\ManagementApi\Client;

class RabbitMqTestExecutionConfigurator implements EndpointTestExecutionConfiguratorInterface,
    EndpointQueuesInformationProviderInterface
{
    /**
     * @var array
     */
    private $connectionCredentials;

    /**
     * @param array $connectionCredentials
     */
    public function __construct(array $connectionCredentials = [])
    {
        $this->connectionCredentials = $connectionCredentials;
    }

    /**
     * @param EndpointConfigurator $endpointConfigurator
     */
    public function configure(EndpointConfigurator $endpointConfigurator)
    {
        /** @var RabbitMqTransportConfigurator $transportConfigurator */
        $transportConfigurator = $endpointConfigurator->useTransport(new RabbitMqTransportDefinition());
        $transportConfigurator->useConnectionCredentials($this->connectionCredentials);
    }

    public function cleanup()
    {
        $rabbitmqClient = new Client();
        $rabbitmqQueue = new Queue($rabbitmqClient);
        $rabbitmqExchange = new Exchange($rabbitmqClient);
        $queues = $rabbitmqQueue->all($this->connectionCredentials['vhost']);
        foreach ($queues as $queue) {
            if (strpos($queue['name'], 'acceptance.PSB') !== false) {
                $rabbitmqQueue->delete($this->connectionCredentials['vhost'], $queue['name']);
            }
        }

        $exchanges = $rabbitmqExchange->all($this->connectionCredentials['vhost']);
        foreach ($exchanges as $exchange) {
            if (strpos($exchange['name'], 'acceptance.PSB') !== false) {
                $rabbitmqExchange->delete($this->connectionCredentials['vhost'], $exchange['name']);
            }
        }
    }

    /**
     * @param string $endpointFqcn
     *
     * @return int
     */
    public function getCountOfMessagesInMainQueueOf($endpointFqcn)
    {
        return $this->getCountOfMessagesInQueueOf($endpointFqcn, false);
    }

    /**
     * @param string $endpointFqcn
     *
     * @return int
     */
    public function getCountOfMessagesInErrorQueueOf($endpointFqcn)
    {
        return $this->getCountOfMessagesInQueueOf($endpointFqcn, true);
    }

    /**
     * @param string $endpointFqcn
     * @param bool   $isErrorQueue
     *
     * @return int
     */
    private function getCountOfMessagesInQueueOf($endpointFqcn, $isErrorQueue)
    {
        $rabbitmqClient = new Client();
        $rabbitmqQueue = new Queue($rabbitmqClient);
        $queues = $rabbitmqQueue->all($this->connectionCredentials['vhost']);
        if ($this->queueExists($this->getQueueName($endpointFqcn, $isErrorQueue), $queues)) {
            $messages = $rabbitmqQueue->retrieveMessages(
                $this->connectionCredentials['vhost'],
                $this->getQueueName($endpointFqcn, $isErrorQueue),
                20,
                true
            );
            return count($messages);
        }

        return 0;
    }

    /**
     * @param string $endpointFqcn
     * @param bool   $isErrorQueue
     *
     * @return string
     */
    private function getQueueName($endpointFqcn, $isErrorQueue)
    {
        return RoutingTopology::getSafeName($endpointFqcn . ($isErrorQueue ? '.Error' : ''));
    }

    /**
     * @param string $needleQueueName
     * @param array  $haystackQueues
     *
     * @return bool
     */
    private function queueExists($needleQueueName, $haystackQueues)
    {
        foreach ($haystackQueues as $queue) {
            if ($queue['name'] == $needleQueueName) {
                return true;
            }
        }
        return false;
    }
}
