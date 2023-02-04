<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class AllScenariosInlineEachFile extends BaseRule {
    protected $violationMessage = 'Scenarios found with preceding spaces "%s", please ensure they are all inline.';

    protected $scenarioLengths = [];

    public function applyOnScenario(Entities\Scenario $scenario, Entities\OutcomeCollection $collection) {
        $title = $scenario->getRawTitle();
        $trimmedTitle = ltrim($title);
        $lengthSpaces = strlen($title) - strlen($trimmedTitle) - 1;

        // Only violations stored based on spaces.
        $this->scenarioLengths[$lengthSpaces] = $scenario;
    }

    public function applyAfterFeature(Entities\FeatureFileContents $contents, Entities\OutcomeCollection $collection) {
        $spacesCount = count($this->scenarioLengths);

        if ($spacesCount > 1) {
            $collection->addOutcome($this->getOutcomeObject(
                1,
                sprintf($this->violationMessage, implode(', ', array_keys($this->scenarioLengths))),
                Entities\Outcome::LOW
            ));
        }

        $this->scenarioLengths = [];
    }
}
