<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NotTooManyExamplesPerScenario extends BaseRule {
    protected $violationMessage = 'To gain reasonable confidence in a feature %d examples usually suffice, got %d. More examples are usually a drain on performance of the wider pack.';

    public function __construct(array $args) {
        $this->maxCount = $args[0];
    }

    public function applyOnScenario(
        Entities\Scenario $scenario,
        Entities\OutcomeCollection $collection
    ) {
        // Examples block contains the placeholder ids as the first column, minus 1 to the total count to adjust for this.
        $examplesCount = count($scenario->examples) - 1;

        if ($examplesCount > $this->maxCount) {
            $collection->addOutcome($this->getOutcomeObject(
                $scenario->lineNumber,
                sprintf($this->violationMessage, $this->maxCount, $examplesCount),
                Entities\Outcome::HIGH
            ));
        }
    }
}
