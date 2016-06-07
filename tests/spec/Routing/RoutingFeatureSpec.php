<?php

namespace spec\PSB\Core\Routing;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Routing\Pipeline\AttachReplyToAddressPipelineStep;
use PSB\Core\Routing\Pipeline\MulticastPublishRoutingConnector;
use PSB\Core\Routing\Pipeline\SubscribeTerminator;
use PSB\Core\Routing\Pipeline\UnicastReplyRoutingConnector;
use PSB\Core\Routing\Pipeline\UnicastSendRoutingConnector;
use PSB\Core\Routing\Pipeline\UnsubscribeTerminator;
use PSB\Core\Routing\RoutingFeature;
use PSB\Core\Routing\UnicastRouterInterface;
use PSB\Core\Transport\Config\TransportInfrastructure;
use PSB\Core\Transport\Config\TransportSubscriptionInfrastructure;
use PSB\Core\Transport\SubscriptionManagerInterface;
use PSB\Core\Util\Settings;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin RoutingFeature
 */
class RoutingFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Routing\RoutingFeature');
    }

    function it_describes_as_being_enabled_by_default()
    {
        $this->describe();
        $this->isEnabledByDefault()->shouldBe(true);
    }

    function it_sets_up_if_endpoint_is_send_only(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $settings->get(KnownSettingsEnum::LOCAL_ADDRESS)->willReturn('irrelevant');
        $settings->get(KnownSettingsEnum::SEND_ONLY)->willReturn(true);

        $builder->defineSingleton(UnicastRouterInterface::class, Argument::type('\Closure'))->shouldBeCalled();
        $pipelineModifications->registerStep(
            'UnicastSendRoutingConnector',
            UnicastSendRoutingConnector::class,
            Argument::type('\Closure')
        )->shouldBeCalled();
        $pipelineModifications->registerStep(
            'UnicastReplyRoutingConnector',
            UnicastReplyRoutingConnector::class,
            Argument::type('\Closure')
        )->shouldBeCalled();
        $pipelineModifications->registerStep(
            'MulticastPublishRoutingConnector',
            MulticastPublishRoutingConnector::class,
            Argument::type('\Closure')
        )->shouldBeCalled();

        $pipelineModifications->registerStep(
            'AttachReplyToAddressPipelineStep',
            AttachReplyToAddressPipelineStep::class,
            Argument::type('\Closure')
        )->shouldNotBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }

    function it_sets_up_if_endpoint_is_not_send_only(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications,
        TransportInfrastructure $transportInfrastructure,
        TransportSubscriptionInfrastructure $transportSubscriptionInfrastructure,
        SimpleCallable $subscriptionManagerFactory
    ) {
        $settings->get(KnownSettingsEnum::LOCAL_ADDRESS)->willReturn('irrelevant');
        $settings->get(KnownSettingsEnum::SEND_ONLY)->willReturn(false);
        $settings->get(TransportInfrastructure::class)->willReturn($transportInfrastructure);
        $transportInfrastructure->configureSubscriptionInfrastructure()->willReturn(
            $transportSubscriptionInfrastructure
        );
        $transportSubscriptionInfrastructure->getSubscriptionManagerFactory()->willReturn($subscriptionManagerFactory);

        $builder->defineSingleton(UnicastRouterInterface::class, Argument::type('\Closure'))->shouldBeCalled();
        $pipelineModifications->registerStep(
            'UnicastSendRoutingConnector',
            UnicastSendRoutingConnector::class,
            Argument::type('\Closure')
        )->shouldBeCalled();
        $pipelineModifications->registerStep(
            'UnicastReplyRoutingConnector',
            UnicastReplyRoutingConnector::class,
            Argument::type('\Closure')
        )->shouldBeCalled();
        $pipelineModifications->registerStep(
            'MulticastPublishRoutingConnector',
            MulticastPublishRoutingConnector::class,
            Argument::type('\Closure')
        )->shouldBeCalled();

        $builder->defineSingleton(
            SubscriptionManagerInterface::class,
            $subscriptionManagerFactory
        )->shouldBeCalled();

        $pipelineModifications->registerStep(
            'AttachReplyToAddressPipelineStep',
            AttachReplyToAddressPipelineStep::class,
            Argument::type('\Closure')
        )->shouldBeCalled();
        $pipelineModifications->registerStep(
            'SubscribeTerminator',
            SubscribeTerminator::class,
            Argument::type('\Closure')
        )->shouldBeCalled();
        $pipelineModifications->registerStep(
            'UnsubscribeTerminator',
            UnsubscribeTerminator::class,
            Argument::type('\Closure')
        )->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
