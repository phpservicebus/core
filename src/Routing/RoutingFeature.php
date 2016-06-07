<?php
namespace PSB\Core\Routing;


use PSB\Core\Feature\Feature;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Outgoing\OutgoingContextFactory;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Routing\Pipeline\AttachReplyToAddressPipelineStep;
use PSB\Core\Routing\Pipeline\MulticastPublishRoutingConnector;
use PSB\Core\Routing\Pipeline\SubscribeTerminator;
use PSB\Core\Routing\Pipeline\UnicastReplyRoutingConnector;
use PSB\Core\Routing\Pipeline\UnicastSendRoutingConnector;
use PSB\Core\Routing\Pipeline\UnsubscribeTerminator;
use PSB\Core\Transport\Config\TransportInfrastructure;
use PSB\Core\Transport\SubscriptionManagerInterface;
use PSB\Core\Util\Settings;

class RoutingFeature extends Feature
{

    /**
     * Method will always be executed and should be used to determine whether to enable or disable the feature,
     * configure default settings, configure dependencies, configure prerequisites and register startup tasks.
     */
    public function describe()
    {
        $this->enableByDefault();
    }

    /**
     * Method is called if all defined conditions are met and the feature is marked as enabled.
     * Use this method to configure and initialize all required components for the feature like
     * the steps in the pipeline or the instances/factories in the container.
     *
     * @param Settings              $settings
     * @param BuilderInterface      $builder
     * @param PipelineModifications $pipelineModifications
     */
    public function setup(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications
    ) {
        $localAddress = $settings->get(KnownSettingsEnum::LOCAL_ADDRESS);

        $builder->defineSingleton(
            UnicastRouterInterface::class,
            function () use ($localAddress, $builder, $settings) {
                return new UnicastRouter(
                    $localAddress,
                    $builder->build(UnicastRoutingTable::class),
                    $settings->get(TransportInfrastructure::class)
                );
            }
        );

        $pipelineModifications->registerStep(
            'UnicastSendRoutingConnector',
            UnicastSendRoutingConnector::class,
            function () use ($builder) {
                return new UnicastSendRoutingConnector(
                    $builder->build(UnicastRouterInterface::class),
                    $builder->build(OutgoingContextFactory::class)
                );
            }
        );
        $pipelineModifications->registerStep(
            'UnicastReplyRoutingConnector',
            UnicastReplyRoutingConnector::class,
            function () use ($builder) {
                return new UnicastReplyRoutingConnector($builder->build(OutgoingContextFactory::class));
            }
        );
        $pipelineModifications->registerStep(
            'MulticastPublishRoutingConnector',
            MulticastPublishRoutingConnector::class,
            function () use ($builder) {
                return new MulticastPublishRoutingConnector($builder->build(OutgoingContextFactory::class));
            }
        );

        $canReceive = !$settings->get(KnownSettingsEnum::SEND_ONLY);
        if ($canReceive) {
            $pipelineModifications->registerStep(
                'AttachReplyToAddressPipelineStep',
                AttachReplyToAddressPipelineStep::class,
                function () use ($localAddress) {
                    return new AttachReplyToAddressPipelineStep($localAddress);
                }
            );

            /** @var TransportInfrastructure $transportInfrastructure */
            $transportInfrastructure = $settings->get(TransportInfrastructure::class);
            $subscriptionManagerFactory = $transportInfrastructure->configureSubscriptionInfrastructure();
            $builder->defineSingleton(
                SubscriptionManagerInterface::class,
                $subscriptionManagerFactory->getSubscriptionManagerFactory()
            );

            $pipelineModifications->registerStep(
                'SubscribeTerminator',
                SubscribeTerminator::class,
                function () use ($builder) {
                    return new SubscribeTerminator($builder->build(SubscriptionManagerInterface::class));
                }
            );
            $pipelineModifications->registerStep(
                'UnsubscribeTerminator',
                UnsubscribeTerminator::class,
                function () use ($builder) {
                    return new UnsubscribeTerminator($builder->build(SubscriptionManagerInterface::class));
                }
            );
        }
    }
}
