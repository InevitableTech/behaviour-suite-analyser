<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class NoSelectorsInSteps extends BaseRule {
    protected $violationMessage = 'Found css/xpath selector "%s" which impact readability and re-usability of steps and make maintenance considerably hard. Consider abstracting these in the library and giving them a meaningful name.';

    public function applyOnStep(Entities\Step $step, Entities\OutcomeCollection $collection) {
        $params = $step->getParameters();

        if (! $params) {
            return;
        }

        foreach ($params as $index => $param) {
            if ($this->isSelector($param)) {
                $collection->addOutcome($this->getStepOutcome(
                    $step,
                    sprintf($this->violationMessage, $param),
                    Entities\Outcome::SERIOUS
                ));
            }
        }
    }

    public function isSelector(string $param): bool {
        $commonTagsString = implode('|', ['html', 'div', 'table', 'tr', 'td', 'th', 'span', 'button', 'input', 'a', 'form']);

        // css selectors
        $cssRegex = "/^($commonTagsString|\s)*(\s?[><+:]?\s?[.#][a-zA-Z0-9\s]+)+/";
        if (preg_match($cssRegex, $param)) {
            return true;
        }

        // xpath selectors
        $xpathRegex = '/^\/(\/.*)+/';
        if (preg_match($xpathRegex, $param)) {
            return true;
        }

        return false;
    }
}
