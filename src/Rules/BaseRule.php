<?php

abstract class BaseRule {
	public function beforeApply($file) {
		return null;
	}

	public function applyOnBackground($background) {
		return null;
	}

	public function beforeApplyOnScenario($scenario) {
		return null;
	}

	public function applyOnScenario($scenario) {
		return null;
	}

	public function afterApplyOnScenario($scenario) {
		return null;
	}

	public function beforeApplyOnStep($step) {
		return null;
	}

	public function applyOnStep($step) {
		return null;
	}

	public function afterApplyOnStep($step) {
		return null;
	}
}
