<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Outcome {
    const LOW = 'low';
    const MEDIUM = 'medium';
    const HIGH = 'high';
    const SERIOUS = 'serious';
    const CRITICAL = 'critical';

    public function __construct(
        string $rule,
        string $file,
        int $line,
        string $message,
        string $severity,
        string $scenario = null,
        string $step = null
    ) {
        $this->rule = $rule;
        $this->file = $file;
        $this->line = $line;
        $this->severity = $severity;
        $this->scenario = $scenario;
        $this->step = $step;
        $this->message = $message;
    }
}