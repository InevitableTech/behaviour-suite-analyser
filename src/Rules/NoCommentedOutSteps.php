<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NoCommentedOutSteps extends BaseRule {
    protected $violationMessage = 'In support of clean code, do not leave behind commented out code.';
    const REASON = '';

    public function applyOnStep(Entities\Step $step, Entities\OutcomeCollection $collection) {
        if (!$step->isActive()) {
            $collection->addOutcome($this->getStepOutcome(
                $step,
                $this->violationMessage,
                Entities\Outcome::MEDIUM
            ));
        }
    }
}