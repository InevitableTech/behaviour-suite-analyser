<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Step {
    public function __construct(int $lineNumber, string $title) {
        $this->lineNumber = $lineNumber;
        $this->title = $title;
    }

    public function getStepDefinition() {
        return trim(preg_replace('/(given|when|then|and|but)/i', '', $this->title));
    }
}