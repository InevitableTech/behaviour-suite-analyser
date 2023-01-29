<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NoScenarioWithoutAssertion extends BaseRule {
    protected $violationMessage = 'Expected to find "Then" step in scenario "%s", are you missing assertions?';

    public function applyOnScenario(Entities\Scenario $scenario, Entities\OutcomeCollection $collection) {
        $steps = $scenario->getActiveSteps();

        foreach ($steps as $index => $step) {
            $keyword = $step->getKeyword();

            if ($keyword === 'then') {
                return;
            }
        }

        $collection->addOutcome($this->getScenarioOutcome(
            $scenario,
            sprintf($this->violationMessage, $scenario->getTitle()),
            Entities\Outcome::SERIOUS
        ));
    }
}
