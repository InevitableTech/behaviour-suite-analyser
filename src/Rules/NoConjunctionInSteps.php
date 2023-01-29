<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NoConjunctionInSteps extends BaseRule {
    protected $violationMessage = 'Found "%s" in step "%s" language, conjunctions indicate hidden complexity in steps and are discouraged. This also makes the step definition complex to maintain and less definitive. Consider splitting the step in 2 or more.';
    const REASON = '';

    public function applyOnStep(Entities\Step $step, Entities\OutcomeCollection $collection) {
        $stepDefinition = $step->getStepDefinition();

        if (preg_match('/.+\s(and|if|or)\s.+/', $stepDefinition, $match)) {
            $collection->addOutcome($this->getStepOutcome(
                $step,
                sprintf($this->violationMessage, $match[1], $stepDefinition),
                Entities\Outcome::SERIOUS
            ));
        }
    }
}
