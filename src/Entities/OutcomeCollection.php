<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class OutcomeCollection extends Collection {
    public $summary = [
        'files' => null,
        'backgrounds' => null,
        'scenarios' => null,
        'activeSteps' => null,
        'activeRules' => null
    ];

    public function addOutcome(Outcome $item) {
        $this->add($item);
    }

    public function addSummary(string $category, string $id) {
        $this->summary[$category][$id] = $id;
    }
}
