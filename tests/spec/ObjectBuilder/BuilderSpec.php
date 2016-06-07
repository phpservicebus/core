<?php

namespace spec\PSB\Core\ObjectBuilder;

use Interop\Container\ContainerInterface as InteropContainerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\ObjectBuilder\Builder;
use PSB\Core\ObjectBuilder\Container;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin Builder
 */
class BuilderSpec extends ObjectBehavior
{
    private $irrelevantId = '';

    function it_is_initializable_with_external_container(
        Container $internalContainer,
        InteropContainerInterface $externalContainer
    ) {
        $this->beConstructedWith($internalContainer, $externalContainer);
        $this->shouldHaveType('PSB\Core\ObjectBuilder\Builder');
    }

    function it_is_initializable_without_external_container(
        Container $internalContainer
    ) {
        $this->beConstructedWith($internalContainer);
        $this->shouldHaveType('PSB\Core\ObjectBuilder\Builder');
    }

    function it_defines_singleton_service_using_the_internal_container(Container $internalContainer)
    {
        $this->beConstructedWith($internalContainer);

        $internalContainer->offsetSet($this->irrelevantId, 'irrelevantvalue')->shouldBeCalled();

        $this->defineSingleton($this->irrelevantId, 'irrelevantvalue');
    }

    function it_defines_factory_using_the_internal_container(
        Container $internalContainer,
        SimpleCallable $factory
    ) {
        $this->beConstructedWith($internalContainer);

        $internalContainer->factory($factory)->shouldBeCalled()->willReturn('irrelevantvalue');
        $internalContainer->offsetSet($this->irrelevantId, 'irrelevantvalue')->shouldBeCalled();

        $this->defineFactory($this->irrelevantId, $factory);
    }

    function it_disposes_of_service_using_the_internal_container(Container $internalContainer)
    {
        $this->beConstructedWith($internalContainer);

        $internalContainer->offsetUnset($this->irrelevantId)->shouldBeCalled();

        $this->dispose($this->irrelevantId);
    }

    function it_checks_if_service_is_defined_using_only_the_internal_container_if_the_external_does_not_exist(
        Container $internalContainer
    ) {
        $this->beConstructedWith($internalContainer);

        $internalContainer->offsetExists($this->irrelevantId)->shouldBeCalled();

        $this->isDefined($this->irrelevantId);
    }

    function it_checks_if_service_is_defined_using_the_external_container_if_not_found_in_internal(
        Container $internalContainer,
        InteropContainerInterface $externalContainer
    ) {
        $this->beConstructedWith($internalContainer, $externalContainer);
        $internalContainer->offsetExists($this->irrelevantId)->willReturn(false);

        $externalContainer->has($this->irrelevantId)->shouldBeCalled();

        $this->isDefined($this->irrelevantId);
    }

    function it_checks_if_service_is_defined_using_only_the_internal_container_if_found_in_internal(
        Container $internalContainer,
        InteropContainerInterface $externalContainer
    ) {
        $this->beConstructedWith($internalContainer, $externalContainer);
        $internalContainer->offsetExists($this->irrelevantId)->willReturn(true);

        $externalContainer->has($this->irrelevantId)->shouldNotBeCalled();

        $this->isDefined($this->irrelevantId);
    }

    function it_builds_if_service_is_defined_using_only_the_internal_container_if_found_in_internal(
        Container $internalContainer
    ) {
        $this->beConstructedWith($internalContainer);

        $internalContainer->offsetExists($this->irrelevantId)->shouldBeCalled()->willReturn(true);
        $internalContainer->offsetGet($this->irrelevantId)->shouldBeCalled()->willReturn('value');

        $this->build($this->irrelevantId)->shouldReturn('value');
    }

    function it_builds_if_service_is_defined_using_the_external_container_if_not_found_in_internal(
        Container $internalContainer,
        InteropContainerInterface $externalContainer
    ) {
        $this->beConstructedWith($internalContainer, $externalContainer);
        $internalContainer->offsetExists($this->irrelevantId)->willReturn(false);

        $externalContainer->has($this->irrelevantId)->shouldBeCalled()->willReturn(true);
        $externalContainer->get($this->irrelevantId)->shouldBeCalled()->willReturn('value');

        $this->build($this->irrelevantId)->shouldReturn('value');
    }

    function it_throws_on_build_if_service_not_found_in_inernal_and_external_does_not_exist(
        Container $internalContainer
    ) {
        $this->beConstructedWith($internalContainer);
        $internalContainer->offsetExists($this->irrelevantId)->willReturn(false);

        $this->shouldThrow('PSB\Core\Exception\ServiceNotFoundException')->duringBuild($this->irrelevantId);
    }

    function it_throws_on_build_if_service_not_found_in_either_container(
        Container $internalContainer,
        InteropContainerInterface $externalContainer
    ) {
        $this->beConstructedWith($internalContainer,$externalContainer);
        $internalContainer->offsetExists($this->irrelevantId)->willReturn(false);
        $externalContainer->has($this->irrelevantId)->willReturn(false);

        $this->shouldThrow('PSB\Core\Exception\ServiceNotFoundException')->duringBuild($this->irrelevantId);
    }
}
