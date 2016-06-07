<?php

namespace spec\PSB\Core\Feature;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\BusContextInterface;
use PSB\Core\Feature\Feature;
use PSB\Core\Feature\FeatureStarter;
use PSB\Core\ObjectBuilder\BuilderInterface;

/**
 * @mixin FeatureStarter
 */
class FeatureStarterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType('PSB\Core\Feature\FeatureStarter');
    }

    function it_starts_the_active_features(
        Feature $activeFeature,
        Feature $inactiveFeature,
        BuilderInterface $builder,
        BusContextInterface $busContext
    ) {
        $this->beConstructedWith([$activeFeature, $inactiveFeature]);
        $activeFeature->isActive()->willReturn(true);
        $inactiveFeature->isActive()->willReturn(false);

        $activeFeature->start($builder, $busContext)->shouldBeCalled();
        $inactiveFeature->start(Argument::any())->shouldNotBeCalled();

        $this->startFeatures($builder, $busContext);
    }
}
