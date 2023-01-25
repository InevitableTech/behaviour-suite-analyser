<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class DiscouragedTags extends BaseRule {
    const VIOLATION_MESSAGE = 'Tag(s) "%s" are discouraged as they leave technical debt behind without any leading information. Instead raise issue in bug tracking system and tag the issue with the unique reference, therefor enabling the wider team to fix the issue.';

    private $tags = ['@dev', '@wip', '@development', '@issue', '@broken', '@fix'];

    public function applyOnFeature(Entities\FeatureFileContents $contents, Entities\OutcomeCollection $collection) {
        $tags = $contents->feature->getTags();
        $intersect = array_intersect($this->tags, $tags);

        if ($intersect) {
            $collection->addOutcome($this->getOutcomeObject(
                1,
                sprintf(self::VIOLATION_MESSAGE, implode(', ', $intersect)),
                Entities\Outcome::HIGH,
                $contents->feature->narrative[0]
            ));
        }
    }

    public function applyOnScenario(Entities\Scenario $scenario, Entities\OutcomeCollection $collection) {
        $tags = $scenario->getTags();
        $intersect = array_intersect($this->tags, $tags);

        if ($intersect) {
            $collection->addOutcome($this->getOutcomeObject(
                $scenario->lineNumber,
                sprintf(self::VIOLATION_MESSAGE, implode(', ', $intersect)),
                Entities\Outcome::HIGH,
                $scenario->scenario[0]
            ));
        }
    }
}