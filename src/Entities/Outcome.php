<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Outcome {
    // Informational.
    const LOW = 0;

    // Cleanup related.
    const MEDIUM = 1;

    // Maintainability issues.
    const HIGH = 2;

    // Reliability/Speed issues.
    const SERIOUS = 3;

    // Architectural issues.
    const CRITICAL = 4;

    public function __construct(
        string $rule,
        string $file,
        int $lineNumber,
        string $message,
        string $severity,
        string $scenario = null,
        string $violatingLine = null,
        string $rawStep = null,
        string $cleanStep = null
    ) {
        $this->rule = $rule;
        $this->file = $file;
        $this->lineNumber = $lineNumber;
        $this->severity = $severity;
        $this->scenario = $scenario;
        $this->violatingLine = $violatingLine;
        $this->rawStep = $rawStep;
        $this->message = $message;
        $this->cleanStep = $cleanStep;
        $this->uniqueScenarioId = $file . ':' . $lineNumber;
    }

    public function getRuleShortName(): string {
        $shortName = explode('\\', $this->rule);
        return end($shortName);
    }
}