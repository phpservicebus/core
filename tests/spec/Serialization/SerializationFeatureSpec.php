<?php

namespace spec\PSB\Core\Serialization;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PSB\Core\ObjectBuilder\BuilderInterface;
use PSB\Core\Pipeline\Incoming\IncomingLogicalMessageFactory;
use PSB\Core\Pipeline\PipelineModifications;
use PSB\Core\Serialization\MessageDeserializerResolver;
use PSB\Core\Serialization\MessageSerializerInterface;
use PSB\Core\Serialization\Pipeline\DeserializeLogicalMessageConnector;
use PSB\Core\Serialization\Pipeline\SerializeMessageConnector;
use PSB\Core\Serialization\SerializationDefinition;
use PSB\Core\Serialization\SerializationFeature;
use PSB\Core\Util\Settings;
use specsupport\PSB\Core\SimpleCallable;

/**
 * @mixin SerializationFeature
 */
class SerializationFeatureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PSB\Core\Serialization\SerializationFeature');
    }

    function it_describes_as_being_enabled_by_defaut()
    {
        $this->describe();
        $this->isEnabledByDefault()->shouldBe(true);
    }

    function it_sets_up(
        Settings $settings,
        BuilderInterface $builder,
        PipelineModifications $pipelineModifications,
        SerializationDefinition $definition,
        SimpleCallable $serializerFactory
    ) {
        $settings->get(SerializationDefinition::class)->willReturn($definition);
        $definition->formalize($settings)->willReturn($serializerFactory);

        $builder->defineSingleton(MessageSerializerInterface::class, $serializerFactory)->shouldBeCalled();
        $builder->defineSingleton(MessageDeserializerResolver::class, Argument::type('\Closure'))->shouldBeCalled();
        $builder->defineSingleton(
            IncomingLogicalMessageFactory::class,
            Argument::type(IncomingLogicalMessageFactory::class)
        )->shouldBeCalled();

        $pipelineModifications->registerStep(
            'DeserializeLogicalMessageConnector',
            DeserializeLogicalMessageConnector::class,
            Argument::type('\Closure')
        )->shouldBeCalled();
        $pipelineModifications->registerStep(
            'SerializeMessageConnector',
            SerializeMessageConnector::class,
            Argument::type('\Closure')
        )->shouldBeCalled();

        $this->setup($settings, $builder, $pipelineModifications);
    }
}
