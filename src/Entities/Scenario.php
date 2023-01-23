<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Scenario {
    public function __construct(int $lineNumber, array $scenario) {
        $this->lineNumber = $lineNumber;
        $this->scenario = $scenario;
        $this->examples = $this->getExamples($scenario);
    }

    public function getSteps(): ?array {
        if (! $this->scenario) {
            return null;
        }

        $steps = array_slice($this->scenario, 1, count($this->scenario));
        $stepObjects = [];
        $table = [];
        $tableStepIndex = false;
        $tableStep = null;

        // Check if a step has a table.
        foreach ($steps as $index => $step) {
            $trimmedStep = trim($step);

            // If we detect the step will have a table
            if (substr($trimmedStep, -1) === ':' && $trimmedStep != 'Examples:') {
                $tableStepIndex = $index;
                $tableStep = $step;
                continue;
            }

            if (strpos($trimmedStep, '|') === 0) {
                // Step table.
                $table[] = $step;
            } else if ($trimmedStep === 'Examples:' || $trimmedStep === '') {
                // Examples statement to be skipped, not considered a step.
                continue;
            } else {
                if ($tableStepIndex) {
                    // Add the definition in with table if found.
                    $stepObjects[$tableStepIndex] = new Step(
                        $this->lineNumber + ($tableStepIndex+1),
                        $steps[$tableStepIndex],
                        $table
                    );

                    $table = [];
                    $tableStepIndex = false;
                }

                $stepObjects[$index] = new Step(
                    $this->lineNumber + ($index+1),
                    $step
                );
            }
        }

        return $stepObjects;
    }

    public function getExamples(array $scenario) {
        $examplesStart = false;
        $examples = [];
        foreach ($scenario as $step) {
            if ($examplesStart) {
                $examples[] = $step;
            }

            if (strpos(trim($step), 'Examples:') === 0) {
                $examplesStart = true;
            }
        }

        return $examples;
    }

    public function getTitle(): ?string {
        if (! $this->scenario) {
            return null;
        }

        return trim(str_replace('Scenario:', '', $this->scenario[0]));
    }

    public function getStepsCount(): int {
        return count($this->getSteps());
    }

    public function getCount(): int {
        return count($this->scenarios);
    }
}