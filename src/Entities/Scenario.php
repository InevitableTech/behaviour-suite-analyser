<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Scenario {
    public function __construct(int $lineNumber, array $scenario) {
        $this->lineNumber = $lineNumber;
        $this->scenario = $scenario;
    }

    public function getSteps(): ?array {
        if (! $this->scenario) {
            return null;
        }

        $steps = array_slice($this->scenario, 1, count($this->scenario));

        return array_map(function($text, $key) {
            return new Step(
                $this->lineNumber + ($key+1),
                $text
            );
        }, $steps, array_keys($steps));
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