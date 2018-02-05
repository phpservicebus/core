<?php

namespace spec\PSB\Core\Pipeline;

use PhpSpec\ObjectBehavior;
use PSB\Core\Exception\PipelineBuildingException;
use PSB\Core\Pipeline\StepChainBuilder;
use PSB\Core\Pipeline\StepRegistration;
use PSB\Core\Pipeline\StepRemoval;
use PSB\Core\Pipeline\StepReplacement;

/**
 * @mixin StepChainBuilder
 */
class StepChainBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('', [], [], []);
        $this->shouldHaveType('PSB\Core\Pipeline\StepChainBuilder');
    }

    function it_builds_a_chain_of_steps_in_stages_delimited_by_connectors1()
    {
        $reg1 = new StepRegistration('id1', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\FirstStageStep');
        $reg2 = new StepRegistration('id2', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\SecondStageStep');
        $reg3 = new StepRegistration(
            'id3',
            'spec\PSB\Core\Pipeline\StepChainBuilderSpec\FirstToSecondStageConnector'
        );
        $reg4 = new StepRegistration(
            'id4',
            'spec\PSB\Core\Pipeline\StepChainBuilderSpec\SecondStageTerminator'
        );
        $this->beConstructedWith('FirstContext', [$reg1, $reg2, $reg3, $reg4], [], []);

        $this->build()->shouldReturn([$reg1, $reg3, $reg2, $reg4]);
    }

    function it_builds_a_chain_of_steps_in_stages_delimited_by_connectors2()
    {
        $reg1 = new StepRegistration('id1', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\FirstStageStep');
        $reg2 = new StepRegistration('id2', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\SecondStageStep');
        $reg3 = new StepRegistration(
            'id3',
            'spec\PSB\Core\Pipeline\StepChainBuilderSpec\FirstToSecondStageConnector'
        );
        $reg4 = new StepRegistration(
            'id4',
            'spec\PSB\Core\Pipeline\StepChainBuilderSpec\SecondStageTerminator'
        );
        $reg5 = new StepRegistration('id5', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\SecondStageStep');
        $this->beConstructedWith('FirstContext', [$reg1, $reg2, $reg3, $reg4, $reg5], [], []);

        $this->build()->shouldReturn([$reg1, $reg3, $reg5, $reg2, $reg4]);
    }

    function it_correctly_orders_steps_within_stages_based_on_dependencies() {
        $reg1 = new StepRegistration('id1', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\FirstStageStep');
        $reg2 = new StepRegistration('id2', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\SecondStageStep');
        $reg2->insertBefore('id5');
        $reg3 = new StepRegistration(
            'id3',
            'spec\PSB\Core\Pipeline\StepChainBuilderSpec\FirstToSecondStageConnector'
        );
        $reg4 = new StepRegistration(
            'id4',
            'spec\PSB\Core\Pipeline\StepChainBuilderSpec\SecondStageTerminator'
        );
        $reg5 = new StepRegistration('id5', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\SecondStageStep');
        $this->beConstructedWith('FirstContext', [$reg1, $reg2, $reg3, $reg4, $reg5], [], []);

        $this->build()->shouldReturn([$reg1, $reg3, $reg2, $reg5, $reg4]);
    }

    function it_throws_if_additions_are_not_unique()
    {
        $this->beConstructedWith(
            '',
            [new StepRegistration('id', 'class1'), new StepRegistration('id', 'class2')],
            [],
            []
        );

        $this->shouldThrow(
            new PipelineBuildingException(
                "Step registration with id 'id' is already registered for step 'class1'."
            )
        )->duringBuild();
    }

    function it_throws_if_replacement_ids_cannot_be_found_in_additions()
    {
        $this->beConstructedWith(
            '',
            [new StepRegistration('id1', 'class1')],
            [new StepReplacement('unfoundId', 'class2')],
            []
        );

        $this->shouldThrow(
            new PipelineBuildingException(
                "You can only replace an existing step registration, 'unfoundId' registration does not exist."
            )
        )->duringBuild();
    }

    function it_throws_if_removal_ids_cannot_be_found_in_additions()
    {
        $this->beConstructedWith(
            '',
            [new StepRegistration('id', 'class')],
            [],
            [new StepRemoval('unfoundId')]
        );

        $this->shouldThrow(
            new PipelineBuildingException(
                "You cannot remove step registration with id 'unfoundId', registration does not exist."
            )
        )->duringBuild();
    }

    function it_throws_if_removals_affect_dependencies()
    {
        $registration1 = new StepRegistration('id1', 'class1');
        $registration1->insertAfter('id2');
        $registration2 = new StepRegistration('id2', 'class2');
        $this->beConstructedWith('', [$registration1, $registration2], [], [new StepRemoval('id2')]);

        $this->shouldThrow(
            new PipelineBuildingException(
                "You cannot remove step registration with id 'id2', registration with id 'id1' depends on it."
            )
        )->duringBuild();
    }

    function it_throws_if_root_context_stage_does_not_exist()
    {
        $registration1 = new StepRegistration('id1', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\FirstStageStep');
        $registration2 = new StepRegistration('id2', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\SecondStageStep');
        $this->beConstructedWith('root', [$registration1, $registration2], [], []);

        $this->shouldThrow(
            new PipelineBuildingException(
                "Can't find any steps/connectors for stage 'root'."
            )
        )->duringBuild();
    }

    function it_throws_if_there_is_more_than_one_connector_per_stage()
    {
        $reg1 = new StepRegistration('id1', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\FirstStageStep');
        $reg2 = new StepRegistration('id2', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\SecondStageStep');
        $reg3 = new StepRegistration(
            'id3',
            'spec\PSB\Core\Pipeline\StepChainBuilderSpec\FirstToSecondStageConnector'
        );
        $reg4 = new StepRegistration(
            'id4',
            'spec\PSB\Core\Pipeline\StepChainBuilderSpec\DuplicateFirstToSecondStageConnector'
        );
        $this->beConstructedWith('FirstContext', [$reg1, $reg2, $reg3, $reg4], [], []);

        $this->shouldThrow(
            new PipelineBuildingException(
                'Multiple stage connectors found for stage \'FirstContext\'. Please remove one of: spec\PSB\Core\Pipeline\StepChainBuilderSpec\FirstToSecondStageConnector,spec\PSB\Core\Pipeline\StepChainBuilderSpec\DuplicateFirstToSecondStageConnector.'
            )
        )->duringBuild();
    }

    function it_throws_if_there_is_no_connector_on_an_intermediary_stage()
    {
        $reg1 = new StepRegistration('id1', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\FirstStageStep');
        $reg2 = new StepRegistration('id2', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\SecondStageStep');
        $this->beConstructedWith('FirstContext', [$reg1, $reg2], [], []);

        $this->shouldThrow(
            new PipelineBuildingException(
                "No stage connector found for stage 'FirstContext'."
            )
        )->duringBuild();
    }

    function it_throws_if_connector_leads_to_nonexistent_stage()
    {
        $reg1 = new StepRegistration('id1', 'spec\PSB\Core\Pipeline\StepChainBuilderSpec\FirstStageStep');
        $reg2 = new StepRegistration(
            'id2',
            'spec\PSB\Core\Pipeline\StepChainBuilderSpec\FirstToSecondStageConnector'
        );
        $this->beConstructedWith('FirstContext', [$reg1, $reg2], [], []);

        $this->shouldThrow(
            new PipelineBuildingException(
                "Can't find any steps/connectors for stage 'SecondContext'."
            )
        )->duringBuild();
    }
}
