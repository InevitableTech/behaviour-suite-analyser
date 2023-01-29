<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NoFeatureWithoutNarrative extends BaseRule {
    protected $violationMessage = 'Feature has no description.';

    public function applyOnFeature(Entities\FeatureFileContents $contents, Entities\OutcomeCollection $collection) {
        $narrative = $contents->feature->getNarrative();

        if (! $narrative) {
            $collection->addOutcome($this->getOutcomeObject(
                1,
                $this->violationMessage,
                Entities\Outcome::DEV
            ));
        }
    }
}
