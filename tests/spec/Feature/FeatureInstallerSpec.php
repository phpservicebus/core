<?php

namespace spec\PSB\Core\Feature;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\Feature\Feature;
use PSB\Core\Feature\FeatureInstaller;
use PSB\Core\KnownSettingsEnum;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Util\Settings;

/**
 * @mixin FeatureInstaller
 */
class FeatureInstallerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType('PSB\Core\Feature\FeatureInstaller');
    }

    function it_does_nothing_if_installers_are_not_enabled(
        Feature $feature,
        BuilderInterface $builder,
        Settings $settings
    ) {
        $this->beConstructedWith([$feature]);
        $settings->tryGet(KnownSettingsEnum::INSTALLERS_ENABLED)->willReturn(false);

        $feature->isActive()->shouldNotBeCalled();

        $this->installFeatures($builder, $settings);
    }

    function it_installs_the_active_features_if_installers_are_enabled(
        Feature $activeFeature,
        Feature $inactiveFeature,
        BuilderInterface $builder,
        Settings $settings
    ) {
        $this->beConstructedWith([$activeFeature, $inactiveFeature]);
        $settings->tryGet(KnownSettingsEnum::INSTALLERS_ENABLED)->willReturn(true);
        $activeFeature->isActive()->willReturn(true);
        $inactiveFeature->isActive()->willReturn(false);

        $activeFeature->install($builder)->shouldBeCalled();
        $inactiveFeature->install(Argument::any())->shouldNotBeCalled();

        $this->installFeatures($builder, $settings);
    }
}
