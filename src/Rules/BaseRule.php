<?php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities\OutcomeCollection;
use Forceedge01\BDDStaticAnalyser\Entities\Outcome;
use Forceedge01\BDDStaticAnalyser\Entities\Background;
use Forceedge01\BDDStaticAnalyser\Entities\Scenario;
use Forceedge01\BDDStaticAnalyser\Entities\Step;
use Forceedge01\BDDStaticAnalyser\Entities\FeatureFileContents;

abstract class BaseRule implements RuleInterface {
	private $featureFileContents = null;

	private $scenario = null;

	public function reset() {
		$this->featureFileContents = null;
		$this->scenario = null;

		return $this;
	}

	public function setFeatureFileContents(FeatureFileContents $contents) {
		$this->featureFileContents = $contents;
	}

	public function setScenario(Scenario $scenario = null) {
		$this->scenario = $scenario;
	}

	public function beforeApply(string $file, OutcomeCollection $collection) {
		return null;
	}

	public function applyOnFeature(FeatureFileContents $contents, OutcomeCollection $collection) {
		return null;
	}

	public function applyOnBackground(Background $background, OutcomeCollection $collection) {
		return null;
	}

	public function beforeApplyOnScenario(Scenario $scenario, OutcomeCollection $collection) {
		return null;
	}

	public function applyOnScenario(Scenario $scenario, OutcomeCollection $collection) {
		return null;
	}

	public function afterApplyOnScenario(Scenario $scenario, OutcomeCollection $collection) {
		$this->setScenario(null);
		return null;
	}

	public function beforeApplyOnStep(Step $step, OutcomeCollection $collection) {
		return null;
	}

	public function applyOnStep(Step $step, OutcomeCollection $collection) {
		return null;
	}

	public function afterApplyOnStep(Step $step, OutcomeCollection $collection) {
		return null;
	}

	public function getOutcomeObject(
		int $lineNumber,
		string $message,
		string $severity,
		string $violatingLine = null,
		string $rawStep = null
	): Outcome {
		return new Outcome(
			static::class,
			$this->featureFileContents->filePath,
			$lineNumber,
			$message,
			$severity,
			$this->scenario ? $this->scenario->getTitle() : null,
			$violatingLine,
			$rawStep
		);
	}
}
