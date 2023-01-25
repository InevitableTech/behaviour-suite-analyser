<?php

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyser\Entities;

class DisplayProcessor implements DisplayProcessorInterface {
	public function displayOutcomes(Entities\OutcomeCollection $outcomes, array $severities) {
		$items = ArrayProcessor::applySeveritiesFilter($outcomes->getItems(), $severities);
		$items = ArrayProcessor::sortByFile($items);
		$items = ArrayProcessor::sortInternalArrayBy($items, 'lineNumber', SORT_ASC);

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

	private function displaySingleOutcomeSummary(int $itemNumber, Entities\Outcome $outcome) {
		echo "   $itemNumber.| [Line: $outcome->lineNumber, Severity: $outcome->severity] - $outcome->message ({$outcome->getRuleShortName()})" . PHP_EOL;

		if ($outcome->rawStep) {
			echo "     | [Step] - $outcome->rawStep" . PHP_EOL;
		}

		echo PHP_EOL;
	}

	public function printSummary(Entities\OutcomeCollection $outcomes, string $reportPath) {
		echo PHP_EOL;
		echo 'Summary' . PHP_EOL;
		echo '----' . PHP_EOL;
		echo "files: {$outcomes->summary['files']}, ";
		echo "backgrounds: {$outcomes->getSummaryCount('backgrounds')}, ";
		echo "scenarios: {$outcomes->getSummaryCount('scenarios')}, ";
		echo "activeSteps: {$outcomes->getSummaryCount('activeSteps')}, ";
		echo "activeRules: {$outcomes->getSummaryCount('activeRules')}." . PHP_EOL;
		echo 'Html report generated: file://' . realpath($reportPath) . PHP_EOL . PHP_EOL;
	}
}
