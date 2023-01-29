<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NoScenarioWithoutDescription extends BaseRule {
    protected $violationMessage = 'Scenario without description, please add description for scenario.';

    public function applyOnScenario(Entities\Scenario $scenario, Entities\OutcomeCollection $collection) {
        $title = $scenario->getTitle();

        if (! $title) {
            $collection->addOutcome($this->getOutcomeObject(
                $scenario->lineNumber,
                $this->violationMessage,
                Entities\Outcome::LOW
            ));
        }
    }
}
