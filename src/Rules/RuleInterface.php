<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities\OutcomeCollection;
use Forceedge01\BDDStaticAnalyser\Entities\Outcome;
use Forceedge01\BDDStaticAnalyser\Entities\Background;
use Forceedge01\BDDStaticAnalyser\Entities\Scenario;
use Forceedge01\BDDStaticAnalyser\Entities\Step;
use Forceedge01\BDDStaticAnalyser\Entities\FeatureFileContents;

interface RuleInterface {
    public function setFeatureFileContents(FeatureFileContents $contents);

    public function setScenario(Scenario $scenario = null);

    public function beforeApply(string $file, OutcomeCollection $collection);

    public function applyOnFeature(FeatureFileContents $contents, OutcomeCollection $collection);

    public function applyOnBackground(Background $background, OutcomeCollection $collection);

    public function beforeApplyOnScenario(Scenario $scenario, OutcomeCollection $collection);

    public function applyOnScenario(Scenario $scenario, OutcomeCollection $collection);

    public function afterApplyOnScenario(Scenario $scenario, OutcomeCollection $collection);

    public function beforeApplyOnStep(Step $step, OutcomeCollection $collection);

    public function applyOnStep(Step $step, OutcomeCollection $collection);

    public function afterApplyOnStep(Step $step, OutcomeCollection $collection);
}
