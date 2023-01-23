<?php

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyser\Rules;
use Forceedge01\BDDStaticAnalyser\Entities;

class RulesProcessor {
	private array $rules = [];
	private array $ruleObjects = [];
	private array $outcome = [];

	public function __construct($rules) {
		$this->rules = $rules;
	}

	/**
	 * All rules are applied everytime on each file.
	 */
	public function applyRules($file, Entities\OutcomeCollection $collection): Entities\OutcomeCollection {
		foreach ($this->rules as $rule => $params) {
			$this->outcome[] = $this->applyRule($file, $this->getRule($rule, $params), $collection);
		}

		return $collection;
	}

	public function getRule(string $rule, array $params = null): Rules\RuleInterface {
		if (isset($this->ruleObjects[$rule])) {
			return $this->ruleObjects[$rule]->reset();
		}

		$this->ruleObjects[$rule] = new $rule($params);

		return $this->ruleObjects[$rule];
	}

	public function applyRule(
		string $file,
		Rules\RuleInterface $rule,
		Entities\OutcomeCollection $collection
	): Entities\OutcomeCollection {
		$rule->beforeApply($file, $collection);

		$contentObject = $this->getFileContent($file);

		$rule->setFeatureFileContents($contentObject);

		if ($contentObject->background) {
			$rule->applyOnBackground($contentObject->background, $collection);
		}

		foreach ($contentObject->scenarios as $scenario) {
			// Scenarios
			$rule->setScenario($scenario);
			$rule->beforeApplyOnScenario($scenario, $collection);
			$rule->applyOnScenario($scenario, $collection);

			// Steps
			$steps = $scenario->getSteps();

			foreach ($steps as $index => $step) {
				$rule->beforeApplyOnStep($step, $collection);
				$rule->applyOnStep($step, $collection);
				$rule->afterApplyOnStep($step, $collection);
			}

			$rule->afterApplyOnScenario($scenario, $collection);
		}

		return $collection;
	}

	private function getFileContent(string $file): Entities\FeatureFileContents {
		$contents = file($file, FILE_IGNORE_NEW_LINES);

		$feature = $this->getFeature($contents);
		$background = $this->getBackground($contents);
		$scenarios = $this->getScenarios($contents);

		return new Entities\FeatureFileContents(
			$contents,
			$file,
			$feature,
			$background,
			$scenarios
		);
	}

	private function isScenarioDeclaration(string $line): bool {
		preg_match('/^Scenario:.*/s', trim($line), $matches);

		if (count($matches) > 0) {
			return true;
		}

		return false;
	}

	private function isBackgroundDeclaration(string $line): bool {
		preg_match('/^Background:.*/s', trim($line), $matches);
		if (count($matches) > 0) {
			return true;
		}

		return false;
	}

	public function getFeature(array $contents): array {
		$feature = [];

		foreach ($contents as $index => $line) {
			if ($this->isBackgroundDeclaration($line) || $this->isScenarioDeclaration($line)) {
				$feature = array_filter(array_slice($contents, 0, $index));
				break;
			}
		}

		return array_values($feature);
	}

	public function getBackground(array $contents): ?Entities\Background {
		$background = [];
		$start = null;
		$end = null;

		foreach ($contents as $index => $line) {
			if ($this->isBackgroundDeclaration($line)) {
				$start = $index;
			}

			if ($this->isScenarioDeclaration($line)) {
				$end = $index;

				// If we've reached a scenario but did not find a background block, then there is none to look for.
				if ($start === null) {
					return null;
				}

				$background = array_filter(array_slice($contents, $start, $end - $start));

				break;
			}
		}

		return new Entities\Background(
			$start+1,
			$background
		);
	}

	public function getScenarios($contents): array {
		$scenarios = [];
		$start = false;
		$startingIndex = 0;
		$endOfFileIndex = count($contents)-1;

		foreach ($contents as $index => $line) {
			if ($this->isScenarioDeclaration($line)) {
				if ($start === true) {
					$scenarioContent = array_filter(array_slice($contents, $startingIndex, $index - $startingIndex));

					$scenarios[] = new Entities\Scenario(
						$startingIndex+1,
						$scenarioContent
					);

					$start = false;
					$startingIndex = 0;
				}

				// Already found?
				if ($start === false) {
					$start = true;
					$startingIndex = $index;
				}
			}

			if ($endOfFileIndex === $index) {
				$scenarios[] = new Entities\Scenario(
					$startingIndex+1,
					array_filter(array_slice($contents, $startingIndex, $index - ($startingIndex-1)))
				);
				break;
			}
		}

		return $scenarios;
	}
}
