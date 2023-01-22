<?php 

class RulesProcessor {
	private array $rules = [];
	private array $outcome = [];

	public function __construct($rules) {
		$this->rules = $rules;
	}

	public function applyRules($file): array {
		foreach ($this->rules as $rule) {
			$this->outcome[] = $this->applyRule($file, $rule);
		}

		return $this->outcome;
	}

	public function applyRule(string $file, BaseRule $rule) {
		$rule->beforeApply($file);

		$background = $this->getBackground($file);
		$rule->applyOnBackground($background);

		$scenarios = $this->getScenarios($file);


		foreach ($scenarios as $scenario) {
			$rule->beforeApplyOnScenario($scenario);
			$rule->applyOnScenario($scenario);

			$steps = $this->getSteps($scenario);

			foreach ($steps as $step) {
				$rule->beforeApplyOnStep($step);
				$rule->applyOnStep($step);
				$rule->afterApplyOnStep($step);
			}

			$rule->afterApplyOnScenario($scenario);
		}
	}

	public function getBackground($file) {

	}

	public function getScenarios($file) {

	}

	public function getSteps($scenario) {

	}
}
