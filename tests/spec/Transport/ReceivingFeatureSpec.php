<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;

use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Transport\Config\TransportReceiveInfrastructure;
use PSB\Core\Transport\InboundTransport;
use PSB\Core\Transport\MessagePusherInterface;
use PSB\Core\Transport\QueueBindings;
use PSB\Core\Transport\QueueCreatorInterface;
use PSB\Core\Transport\ReceivingFeature;
use PSB\Core\Transport\TransportFeature;
use PSB\Core\Util\Settings;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin ReceivingFeature
 */
class ReceivingFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\ReceivingFeature');
    }

    function it_describes_as_being_enabled_by_default_and_depending_on_transport_feature()
    {
        $this->describe();
        $this->isEnabledByDefault()->shouldBe(true);
        $this->getDependencies()->shouldReturn([[TransportFeature::class]]);
    }

    function it_sets_up(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications,
        QueueBindings $queueBindings,
        InboundTransport $inboundTransport,
        TransportReceiveInfrastructure $transportReceiveInfrastructure,
        SimpleCallable $messagePusherFactory,
        SimpleCallable $queueCreatorFactory
    ) {
        $settings->get(QueueBindings::class)->willReturn($queueBindings);
        $settings->get(KnownSettingsEnum::LOCAL_ADDRESS)->willReturn('someaddress');
        $settings->get(InboundTransport::class)->willReturn($inboundTransport);
        $inboundTransport->configure($settings)->willReturn($transportReceiveInfrastructure);
        $transportReceiveInfrastructure->getMessagePusherFactory()->willReturn($messagePusherFactory);
        $transportReceiveInfrastructure->getQueueCreatorFactory()->willReturn($queueCreatorFactory);

        $queueBindings->bindReceiving('someaddress')->shouldBeCalled();

        $builder->defineSingleton(MessagePusherInterface::class, $messagePusherFactory)->shouldBeCalled();
        $builder->defineSingleton(QueueCreatorInterface::class, $queueCreatorFactory)->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
