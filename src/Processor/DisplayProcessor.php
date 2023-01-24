<?php

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyser\Entities;

class DisplayProcessor implements DisplayProcessorInterface {
	public function displayOutcomes(Entities\OutcomeCollection $outcomes, array $severities) {
		$items = $this->applySeveritiesFilter($outcomes, $severities);
		$items = $this->sortByFile($items);
		$items = $this->sortInternalArrayBy($items, 'lineNumber', SORT_ASC);

		echo 'Outcomes' . PHP_EOL;
		echo '----' . PHP_EOL;

		foreach ($items as $file => $outcomeCollection) {
			$violationsCount = count($outcomeCollection);
			echo "-- $file: ($violationsCount Violations)" . PHP_EOL;
			foreach ($outcomeCollection as $index => $outcome) {
				$this->displaySingleOutcomeSummary($index + 1, $outcome);
			}
		}
	}

	public function inputSummary(string $path, string $severities, string $configPath, string $dirToScan) {
		echo 'Input summary' . PHP_EOL;
		echo '----' . PHP_EOL;
		echo 'Scan path: ' . $path . "($dirToScan)" . PHP_EOL;
		echo 'Severities to display: ' . $severities . PHP_EOL;
		echo 'Config path: ' . $configPath . PHP_EOL . PHP_EOL;
	}

	private function applySeveritiesFilter(Entities\OutcomeCollection $outcomes, array $severities): array {
		$items = $outcomes->getItems();
		foreach ($items as $index => $outcome) {
			if (! in_array($outcome->severity, $severities)) {
				unset($items[$index]);
			}
		}

		return $items;
	}

	private function displaySingleOutcomeSummary(int $itemNumber, Entities\Outcome $outcome) {
		echo "   $itemNumber.| [Line: $outcome->lineNumber, Severity: $outcome->severity] - $outcome->message ({$outcome->getRuleShortName()})" . PHP_EOL;

		if ($outcome->rawStep) {
			echo "     | [Step] - $outcome->rawStep" . PHP_EOL;
		}

		echo PHP_EOL;
	}

	public function sortByFile(array $outcomes): array {
		// Sort by file.
		$sorted = [];
		foreach ($outcomes as $items) {
			$sorted[$items->file][] = $items;
		}

		return $sorted;
	}

	public function sortInternalArrayBy(array $sorted, string $column, int $order) {
		// Sort by severity, lineNumber
		foreach ($sorted as $file => $items) {
			$sorted[$file] = $this->sortArray($column, $items, $order);
		}

		return $sorted;
	}

	private function sortArray(string $column, array $items, $sortOrder): array {
		array_multisort(array_column($items, $column), $sortOrder, $items);

		return $items;
	}

	public function printSummary(Entities\OutcomeCollection $outcomes) {
		echo PHP_EOL;
		echo 'Summary' . PHP_EOL;
		echo '----' . PHP_EOL;
		echo "files: {$outcomes->summary['files']}, ";
		echo "backgrounds: {$outcomes->getSummaryCount('backgrounds')}, ";
		echo "scenarios: {$outcomes->getSummaryCount('scenarios')}, ";
		echo "activeSteps: {$outcomes->getSummaryCount('activeSteps')}, ";
		echo "activeRules: {$outcomes->getSummaryCount('activeRules')}." . PHP_EOL;
	}
}
