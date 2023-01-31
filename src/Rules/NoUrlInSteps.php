<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NoUrlInSteps extends BaseRule {
    protected $violationMessage = 'Hardcoded url found, should be abstracted.';

    public function applyOnStep(
        Entities\Step $step,
        Entities\OutcomeCollection $collection
    ) {
        if (! $step->isActive()) {
            return;
        }

        preg_match('/.*https?:\/\/.*/i', $step->title, $match);

        if (count($match) > 0) {
            $collection->addOutcome($this->getStepOutcome(
                $step,
                $this->violationMessage,
                Entities\Outcome::HIGH
            ));
        }
    }
}
