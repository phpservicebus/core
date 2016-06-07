<?php
namespace PSB\Core\Pipeline;


use PSB\Core\Exception\PipelineBuildingException;

class StepChainBuilder
{
    /**
     * @var string
     */
    private $rootContextClass;

    /**
     * @var StepRegistration[]
     */
    private $additions = [];

    /**
     * @var StepReplacement[]
     */
    private $replacements = [];

    /**
     * @var StepRemoval[]
     */
    private $removals = [];

    /**
     * @param string             $rootContextClass
     * @param StepRegistration[] $additions
     * @param StepReplacement[]  $replacements
     * @param StepRemoval[]      $removals
     */
    public function __construct($rootContextClass, array $additions, array $replacements, array $removals)
    {
        $this->rootContextClass = $rootContextClass;
        $this->additions = $additions;
        $this->replacements = $replacements;
        $this->removals = $removals;
    }

    /**
     * @return StepRegistration[]
     *
     * @throws PipelineBuildingException
     */
    public function build()
    {
        $this->assertAdditionsAreUnique();

        /** @var StepRegistration[] $registrations */
        $registrations = [];

        foreach ($this->additions as $addition) {
            $registrations[$addition->getStepId()] = $addition;
        }

        $this->assertReplacementsAreValid($registrations);

        foreach ($this->replacements as $replacement) {
            $registrations[$replacement->getIdToReplace()]->replaceWith($replacement);
        }

        $this->assertRemovalsAreValid($registrations);
        $this->assertRemovalsDoNotAffectDependencies($registrations);

        foreach ($this->removals as $removal) {
            if (isset($registrations[$removal->getIdToRemove()])) {
                unset($registrations[$removal->getIdToRemove()]);
            }
        }

        if (!$registrations) {
            return [];
        }

        $stages = $this->groupRegistrationsByStage($registrations);

        $this->assertContextStageExists($this->rootContextClass, $stages);
        $currentStage = $stages[$this->rootContextClass];
        $stageName = $this->rootContextClass;

        $finalOrder = [];
        $stageNumber = 1;
        while ($currentStage) {
            $stageSteps = $this->getStepsFromStage($currentStage);
            $finalOrder = array_merge($finalOrder, $this->sort($stageSteps));

            $stageConnectors = $this->getConnectorsFromStage($currentStage);

            $this->assertThereIsAtMostOneConnectorPerStage($stageName, $stageConnectors);
            $this->assertThereIsAtLeastOneConnectorPerIntermediaryStage(
                $stageNumber,
                count($stages),
                $stageName,
                $stageConnectors
            );

            $currentStage = null;

            /** @var StepRegistration $stageConnector */
            $stageConnector = reset($stageConnectors);
            if ($stageConnector) {
                $finalOrder[] = $stageConnector;

                if (!$this->isTerminator($stageConnector)) {
                    /** @var StageConnectorInterface $connectorClass */
                    $connectorClass = $stageConnector->getStepFqcn();
                    $stageName = $connectorClass::getNextStageContextClass();
                    $this->assertContextStageExists($stageName, $stages);

                    $currentStage = $stages[$stageName];
                }
            }

            $stageNumber++;
        }

        return $finalOrder;
    }

    /**
     * @param StepRegistration[] $registrations
     *
     * @return array
     */
    private function groupRegistrationsByStage(array $registrations)
    {
        $stages = [];
        foreach ($registrations as $registration) {
            /** @var PipelineStepInterface $stepClass */
            $stepClass = $registration->getStepFqcn();
            $stageName = $stepClass::getStageContextClass();

            if (!isset($stages[$stageName])) {
                $stages[$stageName] = [];
            }

            $stages[$stageName][] = $registration;
        }

        return $stages;
    }

    /**
     * @param StepRegistration[] $currentStage
     * @param bool               $isConnector
     *
     * @return StepRegistration[]
     */
    private function getStepsFromStage(array $currentStage, $isConnector = false)
    {
        $steps = [];
        foreach ($currentStage as $step) {
            if (!$isConnector && !$this->isImplementing($step->getStepFqcn(), StageConnectorInterface::class)) {
                $steps[$step->getStepId()] = $step;
            }

            if ($isConnector && $this->isImplementing($step->getStepFqcn(), StageConnectorInterface::class)) {
                $steps[$step->getStepId()] = $step;
            }
        }

        return $steps;
    }

    /**
     * @param StepRegistration[] $currentStage
     *
     * @return StepRegistration[]
     */
    private function getConnectorsFromStage(array $currentStage)
    {
        return $this->getStepsFromStage($currentStage, true);
    }

    /**
     * @param StepRegistration $stageConnector
     *
     * @return bool
     */
    private function isTerminator($stageConnector)
    {
        return $this->isImplementing($stageConnector->getStepFqcn(), PipelineTerminatorInterface::class);
    }

    /**
     * @param StepRegistration[] $stageSteps
     *
     * @return StepRegistration[]
     */
    private function sort(array $stageSteps)
    {
        if (!$stageSteps) {
            return [];
        }

        $dependencyGraphBuilder = new StepDependencyGraphBuilder($stageSteps);
        return $dependencyGraphBuilder->build()->sort();
    }

    /**
     * @param string $fqcn
     * @param string $fqin
     *
     * @return bool
     */
    private function isImplementing($fqcn, $fqin)
    {
        $interfaces = class_implements($fqcn, true);
        return isset($interfaces[$fqin]);
    }

    /**
     * @throws PipelineBuildingException
     */
    private function assertAdditionsAreUnique()
    {
        /** @var StepRegistration[] $registrations */
        $registrations = [];
        foreach ($this->additions as $addition) {
            if (isset($registrations[$addition->getStepId()])) {
                $existingStepClass = $registrations[$addition->getStepId()]->getStepFqcn();
                throw new PipelineBuildingException(
                    "Step registration with id '{$addition->getStepId()}' is already registered for step '$existingStepClass'."
                );
            }
            $registrations[$addition->getStepId()] = $addition;
        }
    }

    /**
     * @param StepRegistration[] $registrations
     *
     * @throws PipelineBuildingException
     */
    private function assertReplacementsAreValid(array $registrations)
    {
        foreach ($this->replacements as $replacement) {
            if (!isset($registrations[$replacement->getIdToReplace()])) {
                throw new PipelineBuildingException(
                    "You can only replace an existing step registration, '{$replacement->getIdToReplace()}' registration does not exist."
                );
            }
        }
    }

    /**
     * @param StepRegistration[] $registrations
     *
     * @throws PipelineBuildingException
     */
    private function assertRemovalsAreValid(array $registrations)
    {
        foreach ($this->removals as $removal) {
            if (!isset($registrations[$removal->getIdToRemove()])) {
                throw new PipelineBuildingException(
                    "You cannot remove step registration with id '{$removal->getIdToRemove()}', registration does not exist."
                );
            }
        }
    }

    /**
     * @param StepRegistration[] $registrations
     *
     * @throws PipelineBuildingException
     */
    private function assertRemovalsDoNotAffectDependencies(array $registrations)
    {
        $removalIds = [];
        foreach ($this->removals as $removal) {
            $removalIds[$removal->getIdToRemove()] = 0;
        }

        foreach ($registrations as $registration) {
            /** @var StepRegistrationDependency $dependency */
            foreach (array_merge($registration->getBefores(), $registration->getAfters()) as $dependency) {
                if (isset($removalIds[$dependency->getDependsOnId()])) {
                    throw new PipelineBuildingException(
                        "You cannot remove step registration with id '{$dependency->getDependsOnId()}', registration with id '{$registration->getStepId()}' depends on it."
                    );
                }
            }
        }
    }

    /**
     * @param $contextClass
     * @param $stages
     *
     * @throws PipelineBuildingException
     */
    private function assertContextStageExists($contextClass, $stages)
    {
        if (!isset($stages[$contextClass])) {
            throw new PipelineBuildingException(
                "Can't find any steps/connectors for stage '$contextClass'."
            );
        }
    }

    /**
     * @param string             $stageName
     * @param StepRegistration[] $stageConnectors
     *
     */
    private function assertThereIsAtMostOneConnectorPerStage($stageName, $stageConnectors)
    {
        if (count($stageConnectors) > 1) {
            $connectorClasses = [];
            foreach ($stageConnectors as $connector) {
                $connectorClasses[] = $connector->getStepFqcn();
            }
            $connectors = implode(',', $connectorClasses);

            throw new PipelineBuildingException(
                "Multiple stage connectors found for stage '$stageName'. Please remove one of: $connectors."
            );
        }
    }

    /**
     * @param int                $stageNumber
     * @param int                $stageCount
     * @param string             $stageName
     * @param StepRegistration[] $stageConnectors
     *
     */
    private function assertThereIsAtLeastOneConnectorPerIntermediaryStage(
        $stageNumber,
        $stageCount,
        $stageName,
        $stageConnectors
    ) {
        if ($stageNumber < $stageCount && count($stageConnectors) == 0) {
            throw new PipelineBuildingException("No stage connector found for stage '$stageName'.");
        }
    }
}

