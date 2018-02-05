<?php

namespace spec\PSB\Core\Transport;

use PhpSpec\ObjectBehavior;

use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Transport\Config\TransportSendInfrastructure;
use PSB\Core\Transport\MessageDispatcherInterface;
use PSB\Core\Transport\OutboundTransport;
use PSB\Core\Transport\SendingFeature;
use PSB\Core\Transport\TransportFeature;
use PSB\Core\Util\Settings;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin SendingFeature
 */
class SendingFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Transport\SendingFeature');
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
        OutboundTransport $outboundTransport,
        TransportSendInfrastructure $transportSendInfrastructure,
        SimpleCallable $messageDispatcherFactory
    ) {
        $settings->get(OutboundTransport::class)->willReturn($outboundTransport);
        $outboundTransport->configure($settings)->willReturn($transportSendInfrastructure);
        $transportSendInfrastructure->getMessageDispatcherFactory()->willReturn($messageDispatcherFactory);

        $builder->defineSingleton(MessageDispatcherInterface::class, $messageDispatcherFactory)->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
