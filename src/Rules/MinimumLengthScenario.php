<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class MinimumLengthScenario extends BaseRule {
    protected $violationMessage = 'Expected scenario to have a minimum of %d steps in scenario, got %d.';

    public function __construct(array $options) {
        $this->minimumLength = $options[0];
    }

    public function applyOnScenario(Entities\Scenario $scenario, Entities\OutcomeCollection $collection) {
        $steps = $scenario->getActiveSteps();

        if (count($steps) < $this->minimumLength) {
            $collection->addOutcome($this->getScenarioOutcome(
                $scenario,
                sprintf($this->violationMessage, $this->minimumLength, count($steps)),
                Entities\Outcome::CRITICAL,
                $scenario->getTitle()
            ));
        }
    }
}
