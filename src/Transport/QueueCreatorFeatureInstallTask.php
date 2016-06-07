<?php
namespace PSB\Core\Transport;


use PSB\Core\Feature\FeatureInstallTaskInterface;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\Util\Settings;

class QueueCreatorFeatureInstallTask implements FeatureInstallTaskInterface
{
    /**
     * @var QueueCreatorInterface
     */
    private $queueCreator;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param QueueCreatorInterface $queueCreator
     * @param Settings              $settings
     */
    public function __construct(QueueCreatorInterface $queueCreator, Settings $settings)
    {

        $this->queueCreator = $queueCreator;
        $this->settings = $settings;
    }

    public function install()
    {
        $createQueues = $this->settings->tryGet(KnownSettingsEnum::CREATE_QUEUES);
        if ($createQueues || $createQueues === null) {
            $this->queueCreator->createIfNecessary($this->settings->get(QueueBindings::class));
        }
    }
}
