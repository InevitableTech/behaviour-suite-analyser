<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NoLongScenarios extends BaseRule {
    public function __construct(array $args) {
        $this->maxCount = $args[0];
    }

    public function applyOnScenario(
        Entities\Scenario $scenario,
        Entities\OutcomeCollection $collection
    ) {
        if ($scenario->getStepsCount() > $this->maxCount) {
            $collection->addOutcome($this->getOutcomeObject(
                $scenario->lineNumber,
                'This scenario is too long, consider reducing size. The following tactics can be applied to reduce its size...',
                Entities\Outcome::CRITICAL
            ));
        }
    }
}
