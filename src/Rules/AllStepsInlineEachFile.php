<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class AllStepsInlineEachFile extends BaseRule {
    protected $violationMessage = 'Steps found with preceding spaces "%s", please ensure they are all inline.';

    protected $stepLengths = [];

    public function applyOnStep(Entities\Step $step, Entities\OutcomeCollection $collection) {
        $title = $step->getRawTitle();
        $trimmedTitle = ltrim($title);
        $lengthSpaces = strlen($title) - strlen($trimmedTitle);

        // Only violations stored based on spaces.
        $this->stepLengths[$lengthSpaces] = $step;
    }

    public function applyAfterFeature(Entities\FeatureFileContents $contents, Entities\OutcomeCollection $collection) {
        $spacesCount = count($this->stepLengths);

        if ($spacesCount > 1) {
            $collection->addOutcome($this->getOutcomeObject(
                1,
                sprintf($this->violationMessage, implode(', ', array_keys($this->stepLengths))),
                Entities\Outcome::LOW
            ));
        }

        $this->stepLengths = [];
    }
}
