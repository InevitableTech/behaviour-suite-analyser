<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NoUrlInSteps extends BaseRule {
    const VIOLATION_MESSAGE = 'Hardcoded url found, should be abstracted.';

    public function applyOnStep(
        Entities\Step $step,
        Entities\OutcomeCollection $collection
    ) {
        preg_match('/.*https?:\/\/.*/i', $step->title, $match);

        if (count($match) > 0) {
            $collection->addOutcome($this->getOutcomeObject(
                $step->lineNumber,
                self::VIOLATION_MESSAGE,
                Entities\Outcome::HIGH,
                $step->getStepDefinition()
            ));
        }
    }
}
