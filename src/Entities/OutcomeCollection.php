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

    public function setItems(array $items) {
        $this->items = $items;
    }

    public function addOutcome(Outcome $item) {
        $this->add($item);
    }

    public function addSummary(string $category, string $id) {
        $this->summary[$category][$id] = $id;
    }

    public function getSummaryCount($key) {
        if (! isset($this->summary[$key])) {
            return 0;
        }

        return count($this->summary[$key]);
    }
}
