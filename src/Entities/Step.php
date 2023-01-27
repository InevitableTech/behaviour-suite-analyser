<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Step {
    public function __construct(int $lineNumber, string $title, array $table = []) {
        $this->lineNumber = $lineNumber;
        $this->title = $title;
        $this->trimmedTitle = trim($title);
        $this->table = $table;
        $this->parameters = $this->extractParameters();
    }

    public function getStepDefinition() {
        // Remove keyword and space.
        $filtered = trim(preg_replace('/^#?\s*(given|when|then|and|but)/i', '', $this->trimmedTitle));

        // Remove params.
        return preg_replace(['/\d+/i', '/"([^"]*)"/is'], ['{num}', '"{string}"'], $filtered);
    }

    public function getParameters(): array {
        return $this->parameters;
    }

    public function extractParameters($quote = '"') {
        $pattern = "/$quote([^$quote]*)$quote/";
        preg_match_all($pattern, $this->trimmedTitle, $matches);

        return $matches[1];
    }

    public function getKeyword(): string {
        $match =[];
        preg_match('/^#?\s*(given|when|then|and|but)/i', $this->trimmedTitle, $match);

        return strtolower($match[0]);
    }

    public function isActive(): bool {
        if (strpos($this->trimmedTitle, '#') === 0 || strpos($this->trimmedTitle, '//') === 0) {
            return false;
        }

        return true;
    }
}