<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Scenario {
    public function __construct(int $lineNumber, array $scenario, bool $active = true) {
        $this->lineNumber = $lineNumber;
        $this->scenario = $scenario;
        $this->examples = $this->getExamples($scenario);
        $this->active = $active;
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

            // Strip out any comments within the content.
            if (! $this->isStepDefinition($trimmedStep)) {
                continue;
            }

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

        return array_values($stepObjects);
    }

    public function getActiveSteps() {
        $steps = $this->getSteps();

        foreach ($steps as $index => $step) {
            if (! $step->isActive()) {
                unset($steps[$index]);
            }
        }

        return array_values($steps);
    }

    public function isStepDefinition(string $step): bool {
        return preg_match('/^#?\s*(given|when|then|and|but)\s.*/i', trim($step));
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

        if ($this->getTags()) {
            return trim(str_replace('Scenario:', '', $this->scenario[1]));
        }

        return trim(str_replace('Scenario:', '', $this->scenario[0]));
    }

    public function getStepsCount(): int {
        return count($this->getSteps());
    }

    public function getTags(): array {
        preg_match('/^@.*/', $this->scenario[0], $matches);
        if (count($matches) > 0) {
            return explode(' ', $this->scenario[0]);
        }

        return [];
    }
}