<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NoLongScenarios extends BaseRule {
    protected $violationMessage = 'This scenario is too long, consider reducing size to %d lines. The following tactics can be applied to reduce its size...';

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
                sprintf($this->violationMessage, $this->maxCount),
                Entities\Outcome::CRITICAL,
                $scenario->getTitle()
            ));
        }
    }
}
