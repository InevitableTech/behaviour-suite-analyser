<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NoDuplicateScenarios extends BaseRule {
    protected $violationMessage = 'Found a duplicate scenario "%s"';

    protected $scenarios = [];

    public function applyOnScenario(Entities\Scenario $scenario, Entities\OutcomeCollection $collection) {
        $title = $scenario->getTitle();
        if (in_array($title, $this->scenarios)) {
            $collection->addOutcome($this->getOutcomeObject(
                $scenario->lineNumber,
                sprintf($this->violationMessage, $title),
                Entities\Outcome::LOW,
                'Scenario: ' . $title
            ));
        }


        $this->scenarios[] = $title;
    }
}
