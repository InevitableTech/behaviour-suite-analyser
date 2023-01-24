<?php

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyser\Entities;

class DisplayProcessor implements DisplayProcessorInterface {
	public function displayOutcomes(Entities\OutcomeCollection $outcomes) {
		$items = $this->sortByFile($outcomes);
		$items = $this->sortInternalArrayBy($items, 'lineNumber', SORT_ASC);

		foreach ($items as $file => $outcomeCollection) {
			echo "-- $file:" . PHP_EOL;
			foreach ($outcomeCollection as $outcome) {
				echo "    [Severity: $outcome->severity - line: $outcome->lineNumber] - $outcome->message ({$outcome->getRuleShortName()})" . PHP_EOL;

				if ($outcome->rawStep) {
					echo "    Step: $outcome->rawStep" . PHP_EOL;
				}

				echo PHP_EOL;
			}
		}
	}

	public function sortByFile(Entities\OutcomeCollection $outcomes): array {
		// Sort by file.
		$sorted = [];
		foreach ($outcomes->getItems() as $items) {
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
		echo 'Summary:' . PHP_EOL;
		echo '----' . PHP_EOL;
		echo "files: {$outcomes->summary['files']}, ";
		echo "backgrounds: {$outcomes->getSummaryCount('backgrounds')}, ";
		echo "scenarios: {$outcomes->getSummaryCount('scenarios')}, ";
		echo "activeSteps: {$outcomes->getSummaryCount('activeSteps')}, ";
		echo "activeRules: {$outcomes->getSummaryCount('activeRules')}." . PHP_EOL;
	}
}
