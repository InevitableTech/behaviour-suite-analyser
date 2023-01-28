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
            echo PHP_EOL . "-- $file: ($violationsCount Violations)" . PHP_EOL . PHP_EOL;
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

    public function printSummary(Entities\OutcomeCollection $outcomes, string $reportPath) {
        $violationsCount = count($outcomes->getItems());
        echo PHP_EOL;
        echo 'Summary' . PHP_EOL;
        echo '----' . PHP_EOL;
        echo 'Violations: ' . $violationsCount . ', ';
        echo "files: {$outcomes->summary['files']}, ";
        echo "backgrounds: {$outcomes->getSummaryCount('backgrounds')}, ";
        echo "scenarios: {$outcomes->getSummaryCount('scenarios')}, ";
        echo "activeSteps: {$outcomes->getSummaryCount('activeSteps')}, ";
        echo "activeRules: {$outcomes->getSummaryCount('activeRules')}." . PHP_EOL;
        echo 'Html report generated: file://' . realpath($reportPath) . PHP_EOL . PHP_EOL;
    }

    public function helpMenu(array $options, Entities\Config $config) {
        echo 'Help menu' . PHP_EOL;
        echo '----' . PHP_EOL;
        foreach ($options as $option => $description) {
            echo '-' . rtrim($option,':') . '     ' . $description . PHP_EOL;
        }

        echo PHP_EOL;
    }

    public function handleOptions(array $input, $config, array $options, string $version) {
        if (isset($input['h'])) {
            $this->helpMenu($options, $config);
            exit;
        }

        if (isset($input['v'])) {
            echo 'Version: ' . $version . PHP_EOL;
            exit;
        }

        if (isset($input['i'])) {
            echo 'Initialising new config file' . PHP_EOL;
            if (copy($config->path, './config.php')) {
                echo '+ config.php' . PHP_EOL;
            }else {
                throw new Exception('Unable to create config file, likely due to permissions. Copy the following contents into a config.php file manually:' . PHP_EOL . PHP_EOL . file_get_contents($config->path));
            }
            exit;
        }

        if (isset($input['r'])) {
            echo 'Active Rules ' . count($config->get('rules')) . PHP_EOL;
            print_r($config->get('rules'));
            exit;
        }
    }

    private function displaySingleOutcomeSummary(int $itemNumber, Entities\Outcome $outcome) {
        $length = strlen((string) $itemNumber);
        $spaces = str_repeat(' ', $length);

        echo "   {$itemNumber}.| [Line: $outcome->lineNumber, Severity: $outcome->severity] - $outcome->message ({$outcome->getRuleShortName()})" . PHP_EOL;

        if ($outcome->violatingLine) {
            echo "    $spaces| [Step] - $outcome->violatingLine" . PHP_EOL;
        }

        echo PHP_EOL;
    }
}
