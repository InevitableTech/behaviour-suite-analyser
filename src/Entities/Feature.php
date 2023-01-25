<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class Feature {
    public function __construct(array $narrative) {
        $this->narrative = $narrative;
    }

    public function getTags(): array {
        preg_match('/^@.*/', $this->narrative[0], $matches);
        if (count($matches) > 0) {
            return explode(' ', $this->narrative[0]);
        }

        return [];
    }
}