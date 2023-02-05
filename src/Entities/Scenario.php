<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

use Forceedge01\BDDStaticAnalyser\Processor;

class Scenario
{
    public function __construct(int $lineNumber, array $scenario, bool $active = true)
    {
        $this->lineNumber = $lineNumber;
        $this->scenario = $scenario;
        $this->examples = $this->getExamples($this->removePyStrings($scenario));
        $this->active = $active;
    }

    public function removePyStrings(array $scenario): array {
        $pyString = Processor\ArrayProcessor::getContentBetween('/^\s*"""\s*$/', '/^\s*"""\s*$/', $scenario);

        return array_diff($scenario, $pyString);
    }

    public function getSteps(): ?array {
        if (! $this->scenario) {
            return null;
        }

        $steps = $this->removeTagsAndScenarioFromSteps();

        $stepObjects = [];
        $table = [];
        $pyString = [];
        $pyStringStep = '';
        $pyStringStartIndex = false;
        $tableOrPyStringStepIndex = false;

        // Check if a step has a table.
        foreach ($steps as $index => $step) {
            $trimmedStep = trim($step);

            // Strip out any comments within the content.
            if ($trimmedStep === '' || $this->isComment($trimmedStep)) {
                continue;
            }

            // If we detect the step will have a table or pyString.
            if ($this->isTabledOrPyStringedStep($trimmedStep)) {
                $tableOrPyStringStepIndex = $index;

                // Defer adding until we've got the table or pystring.
                continue;
            } elseif ($this->isPyStringBlock($trimmedStep)) { // If we're dealing with a PyString.
                if (! $pyStringStartIndex) {
                    $pyStringStartIndex = $index;
                } elseif ($pyStringStartIndex) {
                    // This ending marks the end of the step?
                    $stepObjects[$pyStringStartIndex] = new Step(
                        $this->lineNumber + ($tableOrPyStringStepIndex+1),
                        $steps[$tableOrPyStringStepIndex],
                        [],
                        $pyString
                    );

                    $pyString = [];
                    $pyStringStartIndex = false;
                    $tableOrPyStringStepIndex = false;
                }

                // We skip the start and end quotes of the pystring block.
                continue;
            } elseif ($pyStringStartIndex !== false) { // Until the pystring closes, we keep adding it in.
                $pyString[] = $step;
                continue;
            } elseif ($this->isTableBlock($trimmedStep)) { // If we're dealing with a table (example or otherwise).
                // Step table.
                $table[] = $step;
            } elseif ($this->isExampleBlock($trimmedStep)) {
                // Examples statement to be skipped, not considered a step.
                continue;
            } else {
                // Add the step, processes any previous data as well.
                if ($tableOrPyStringStepIndex) {
                    // Add the definition in with table if found.
                    $stepObjects[$tableOrPyStringStepIndex] = new Step(
                        $this->lineNumber + ($tableOrPyStringStepIndex+1),
                        $steps[$tableOrPyStringStepIndex],
                        $table,
                        []
                    );

                    $table = [];
                    $tableOrPyStringStepIndex = false;
                }

                $stepObjects[$index] = new Step(
                    $this->lineNumber + ($index+1),
                    $step
                );
            }
        }

        if ($tableOrPyStringStepIndex !== false) {
            $stepObjects[$tableOrPyStringStepIndex] = new Step(
                $this->lineNumber + ($tableOrPyStringStepIndex+1),
                $steps[$tableOrPyStringStepIndex],
                $table,
                $pyString
            );
        }

        return Processor\ArrayProcessor::cleanArray($stepObjects);
    }

    private function isComment(string $line): bool {
        return preg_match('/^#.*/', $line);
    }

    private function isPyStringBlock($line): bool {
        return $line === '"""';
    }

    private function isExampleBlock($line): bool {
        return $line === 'Examples:';
    }

    private function isTabledOrPyStringedStep($line): bool {
        return $this->isStepDefinition($line) && substr($line, -1) === ':';
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

            if ($this->isExampleBlock(trim($step))) {
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
            return Processor\ArrayProcessor::cleanArray(explode(' ', $this->scenario[0]));
        }

        return [];
    }
}
