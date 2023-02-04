<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

use Forceedge01\BDDStaticAnalyser\Processor;

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

        $steps = $this->removeTagsAndScenarioFromSteps();

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
            if (!$this->isExampleBlock($trimmedStep) && $this->isTabledStep($trimmedStep)) {
                $tableStepIndex = $index;
                $tableStep = $step;
                continue;
            }

            if ($this->isTableBlock($trimmedStep)) {
                // Step table.
                $table[] = $step;
            } else if ($this->isExampleBlock($trimmedStep) || $trimmedStep === '') {
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

        return Processor\ArrayProcessor::cleanArray($stepObjects);
    }

    private function isExampleBlock($line): bool {
        return $line === 'Examples:';
    }

    private function isTabledStep($line): bool {
        return substr($line, -1) === ':';
    }

    private function isTableBlock($line): bool {
        return strpos($line, '|') === 0;
    }

    public function removeTagsAndScenarioFromSteps(): array {
        $tags = $this->getTags();
        $scenario = $this->scenario;

        // Check if we have tags, is so strip them out.
        if ($tags) {
            unset($scenario[0]);
            $scenarios = array_values($scenario);
        }

        // Remove first line that says scenario:.
        return array_slice($scenario, 1, count($scenario));
    }

    public function getActiveSteps(): array {
        $steps = $this->getSteps();

        foreach ($steps as $index => $step) {
            if (! $step->isActive()) {
                unset($steps[$index]);
            }
        }

        return Processor\ArrayProcessor::cleanArray($steps);
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

        return Processor\ArrayProcessor::cleanArray($examples);
    }

    public function getTitle(): string {
        return trim($this->getRawTitle());
    }

    public function getRawTitle(): string {
        if (! $this->scenario) {
            return '';
        }

        if ($this->getTags()) {
            return str_replace('Scenario:', '', $this->scenario[1]);
        }

        return str_replace('Scenario:', '', $this->scenario[0]);
    }

    public function getStepsCount(): int {
        return count($this->getSteps());
    }

    public function getTags(): array {
        preg_match('/^@.*/', trim($this->scenario[0]), $matches);
        if (count($matches) > 0) {
            return explode(' ', $this->scenario[0]);
        }

        return [];
    }
}
