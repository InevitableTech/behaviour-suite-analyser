<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NoLongScenarios extends BaseRule {
    const VIOLATION_MESSAGE = 'This scenario is too long, consider reducing size. The following tactics can be applied to reduce its size...';

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
                self::VIOLATION_MESSAGE,
                Entities\Outcome::CRITICAL,
                $scenario->getTitle()
            ));
        }
    }
}
