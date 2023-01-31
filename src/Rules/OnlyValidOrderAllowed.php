<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class OnlyValidOrderAllowed extends BaseRule {
    protected $violationMessage = 'Expected step to start with keyword "%s", got "%s" instead. Are you missing a "%s" step?';

    public function applyOnScenario(Entities\Scenario $scenario, Entities\OutcomeCollection $collection) {
        $steps = $scenario->getActiveSteps();
        $expected = [
            'given',
            'when',
            'then',
            'but'
        ];

        // A background can cause a scenario to start with when.
        if ($steps[0]->getKeyword() == 'when') {
            $expected = array_shift($expected);
        }

        $current = 0;
        foreach ($steps as $index => $step) {
            $keyword = $step->getKeyword();

            // The first check must be done prior to the and condition.
            if ($index === 0) {
                if ($keyword == $expected[$current]) {
                    $current++;
                    continue;
                } else {
                    $collection->addOutcome($this->getStepOutcome(
                        $step,
                        sprintf($this->violationMessage, $expected[$current], $keyword, $expected[$current]),
                        Entities\Outcome::MEDIUM
                    ));
                    break;
                }
            } elseif ($keyword === 'and') {
                continue;
            } elseif ($keyword == $expected[$current]) {
                $current++;
            } else {
                $collection->addOutcome($this->getStepOutcome(
                    $step,
                    sprintf($this->violationMessage, $expected[$current], $keyword, $expected[$current]),
                    Entities\Outcome::MEDIUM
                ));
                break;
            }
        }
    }
}
