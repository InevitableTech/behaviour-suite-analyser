<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class DiscouragedTags extends BaseRule {
    protected $violationMessage = 'Tag(s) "%s" are discouraged as they leave technical debt behind without any leading information. Instead raise issue in bug tracking system and tag the issue with the unique reference, therefor enabling the wider team to fix the issue.';

    private $tags = ['@dev', '@wip', '@development', '@broken', '@fix'];

    public function applyOnFeature(Entities\FeatureFileContents $contents, Entities\OutcomeCollection $collection) {
        $tags = $contents->feature->getTags();
        $intersect = array_intersect($this->tags, $tags);

        if ($intersect) {
            $collection->addOutcome($this->getOutcomeObject(
                1,
                sprintf($this->violationMessage, implode(', ', $intersect)),
                Entities\Outcome::HIGH,
                implode(' ', $contents->feature->getTags())
            ));
        }
    }

    public function applyOnScenario(Entities\Scenario $scenario, Entities\OutcomeCollection $collection) {
        $tags = $scenario->getTags();
        $intersect = array_intersect($this->tags, $tags);

        if ($intersect) {
            $collection->addOutcome($this->getOutcomeObject(
                $scenario->lineNumber,
                sprintf($this->violationMessage, implode(', ', $intersect)),
                Entities\Outcome::HIGH,
                $scenario->scenario[0]
            ));
        }
    }
}