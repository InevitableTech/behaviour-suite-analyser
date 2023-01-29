<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NoEmptyFeature extends BaseRule {
    protected $violationMessage = 'Feature file "%s" does not contain any scenarios, are you missing coverage?';

    public function applyOnFeature(Entities\FeatureFileContents $contents, Entities\OutcomeCollection $collection) {
        $scenariosCount = $contents->scenarios;

        if (count($scenariosCount) === 0) {
            $collection->addOutcome($this->getOutcomeObject(
                1,
                sprintf($this->violationMessage, $contents->filePath),
                Entities\Outcome::SERIOUS
            ));
        }
    }
}
