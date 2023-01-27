<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class OnlyValidOrderAllowed extends BaseRule {
    const VIOLATION_MESSAGE = 'Expected step to start with keyword "%s", got "%s" instead. Are you missing a "%s" step?';

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
            print_r($steps);
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
                        sprintf(self::VIOLATION_MESSAGE, $expected[$current], $keyword, $expected[$current]),
                        Entities\Outcome::MEDIUM
                    ));
                    break;
                }
            }

            if ($keyword === 'and') {
                continue;
            }

            if ($keyword == $expected[$current]) {
                $current++;
            } else {
                $collection->addOutcome($this->getStepOutcome(
                    $step,
                    sprintf(self::VIOLATION_MESSAGE, $expected[$current], $keyword, $expected[$current]),
                    Entities\Outcome::MEDIUM
                ));
                break;
            }
        }
    }
}
