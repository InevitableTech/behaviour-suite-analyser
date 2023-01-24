<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Outcome {
    const LOW = 0;
    const MEDIUM = 1;
    const HIGH = 2;
    const SERIOUS = 3;
    const CRITICAL = 4;

    public function __construct(
        string $rule,
        string $file,
        int $lineNumber,
        string $message,
        string $severity,
        string $scenario = null,
        string $step = null,
        string $rawStep = null
    ) {
        $this->rule = $rule;
        $this->file = $file;
        $this->lineNumber = $lineNumber;
        $this->severity = $severity;
        $this->scenario = $scenario;
        $this->step = $step;
        $this->rawStep = $rawStep;
        $this->message = $message;
        $this->uniqueScenarioId = $file . ':' . $lineNumber;
    }

    public function getRuleShortName(): string {
        $shortName = explode('\\', $this->rule);
        return end($shortName);
    }
}