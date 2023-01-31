<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class OnlyFirstPersonLanguageAllowed extends BaseRule {
    protected $violationMessage = 'Steps should be written in first person pov only. This makes the scenario more relatable to yourself and encourages you to put yourself into the users position when writing or reading the scenario.';

    public function __construct(array $keywords = ['given', 'when', 'then', 'and', 'but']) {
        $this->keywords = $keywords;
    }

    public function applyOnScenario(Entities\Scenario $scenario, Entities\OutcomeCollection $collection) {
        $steps = $scenario->getSteps();
        foreach ($steps as $step) {
            $keyword = $step->getKeyword();

            if (in_array($keyword, $this->keywords)) {
                if (strpos($step->getStepDefinition(), 'I ') !== 0) {
                    $collection->addOutcome($this->getStepOutcome(
                        $step,
                        $this->violationMessage,
                        Entities\Outcome::MEDIUM
                    ));
                }
            }
        }
    }
}
