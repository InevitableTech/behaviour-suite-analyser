<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Step {
    public function __construct(int $lineNumber, string $title, array $table = []) {
        $this->lineNumber = $lineNumber;
        $this->title = $title;
        $this->trimmedTitle = trim($title);
        $this->table = $table;
    }

    public function getStepDefinition() {
        // Remove keyword and space.
        $filtered = trim(preg_replace('/(given|when|then|and|but)/i', '', $this->title));

        // Remove params.
        return preg_replace(['/\d+/i', '/".*"/is'], ['<num>', '<string>'], $filtered);
    }

    public function isActive(): bool {
        if (strpos($this->trimmedTitle, '#') === 0 || strpos($this->trimmedTitle, '//') === 0) {
            return false;
        }

        return true;
    }
}