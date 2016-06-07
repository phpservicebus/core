<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\Transport\QueueBindings;
use PSB\Core\Transport\QueueCreatorFeatureInstallTask;
use PSB\Core\Transport\QueueCreatorInterface;
use PSB\Core\Util\Settings;

/**
 * @mixin QueueCreatorFeatureInstallTask
 */
class QueueCreatorFeatureInstallTaskSpec extends ObjectBehavior
{
    /**
     * @var QueueCreatorInterface
     */
    private $queueCreator;

    /**
     * @var Settings
     */
    private $settings;

    function let(QueueCreatorInterface $queueCreator, Settings $settings)
    {
        $this->queueCreator = $queueCreator;
        $this->settings = $settings;
        $this->beConstructedWith($queueCreator, $settings);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\QueueCreatorFeatureInstallTask');
    }

    function it_does_not_create_queues_if_queue_creation_is_disabled()
    {
        $this->settings->tryGet(KnownSettingsEnum::CREATE_QUEUES)->willReturn(false);

        $this->queueCreator->createIfNecessary(Argument::any())->shouldNotBeCalled();

        $this->install();
    }

    function it_creates_queues_if_queue_creation_not_explicitly_disabled(QueueBindings $bindings)
    {
        $this->settings->tryGet(KnownSettingsEnum::CREATE_QUEUES)->willReturn(null);
        $this->settings->get(QueueBindings::class)->willReturn($bindings);

        $this->queueCreator->createIfNecessary($bindings)->shouldBeCalled();

        $this->install();
    }

    function it_creates_queues_if_queue_creation_is_explicitly_enabled(QueueBindings $bindings)
    {
        $this->settings->tryGet(KnownSettingsEnum::CREATE_QUEUES)->willReturn(true);
        $this->settings->get(QueueBindings::class)->willReturn($bindings);

        $this->queueCreator->createIfNecessary($bindings)->shouldBeCalled();

        $this->install();
    }
}
