<?php declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyserRules\Entities;
use Symfony\Component\Console\Output\OutputInterface;

class DisplayProcessor implements DisplayProcessorInterface
{
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function inputSummary(string $path, string $severities, string $configPath, string $dirToScan)
    {
        $this->output->writeln('<info>Input summary');
        $this->output->writeln('----</info>');

        $this->output->writeln('Scan path: ' . $path . "($dirToScan)");
        $this->output->writeln('Severities to display: ' . $severities);
        $this->output->writeln('Config path: ' . $configPath . PHP_EOL);
    }

    public function displayOutcomes(Entities\OutcomeCollection $outcomes, array $severities)
    {
        $items = ArrayProcessor::applySeveritiesFilter($outcomes->getItems(), $severities);
        $items = ArrayProcessor::sortByFile($items);
        $items = ArrayProcessor::sortInternalArrayBy($items, 'lineNumber', SORT_ASC);

        $this->output->writeln('<info>Outcomes');
        $this->output->writeln('----</info>');

        if (count($items) == 0) {
            $this->output->writeln(PHP_EOL . '<comment>No violations detected.</comment>');
        }

        foreach ($items as $file => $outcomeCollection) {
            $violationsCount = count($outcomeCollection);
            $this->output->writeln(PHP_EOL . "<comment>-- $file</comment>: (<error>$violationsCount Violations</error>)" . PHP_EOL);
            foreach ($outcomeCollection as $index => $outcome) {
                $this->displaySingleOutcomeSummary($index + 1, $outcome, $this->output);
            }
        }
    }

    public function printSummary(Entities\OutcomeCollection $outcomes, string $reportPath)
    {
        $violationsCount = count($outcomes->getItems());
        $violationsByRule = $this->getViolationsByRule($outcomes);

        $this->output->write(PHP_EOL);
        $this->output->writeln('<info>Summary');
        $this->output->writeln('----</info>');
        $errorOrSuccess = $violationsCount > 0 ? 'error' : 'info';

        $this->output->writeln(
            "Violations: <$errorOrSuccess>$violationsCount</$errorOrSuccess>, " .
            "files: {$outcomes->summary['files']}, " .
            "backgrounds: {$outcomes->getSummaryCount('backgrounds')}, " .
            "scenarios: {$outcomes->getSummaryCount('scenarios')}, " .
            "activeSteps: {$outcomes->getSummaryCount('activeSteps')}, " .
            "activeRules: {$outcomes->getSummaryCount('activeRules')}."
        );

        if ($violationsCount) {
            $this->output->writeln(
                'Most violated rules: ' . ArrayProcessor::implodeWithKeys(array_slice($violationsByRule, 0, 3), ': ', ', ')
            );
        }

        $this->output->writeln('Html report generated: <comment>file://' . realpath($reportPath) . '</comment>' . PHP_EOL);
    }

    private function displaySingleOutcomeSummary(int $itemNumber, Entities\Outcome $outcome, OutputInterface $output)
    {
        $length = strlen((string) $itemNumber);
        $spaces = str_repeat(' ', $length);

        $output->writeln("   {$itemNumber}.| [Line: <comment>$outcome->lineNumber</comment>, Severity: $outcome->severity] - $outcome->message (<info>{$outcome->getRuleShortName()}</info>)");

        if ($outcome->violatingLine) {
            $output->writeln("    $spaces| [Violating line] - <comment>$outcome->violatingLine</comment>");
        }

        $output->write(PHP_EOL);
    }

    private function getViolationsByRule(Entities\OutcomeCollection $collection): array
    {
        $counts = [];
        foreach ($collection->getItems() as $outcome) {
            if (!isset($counts[$outcome->getRuleShortName()])) {
                $counts[$outcome->getRuleShortName()] = 1;
                continue;
            }

            $counts[$outcome->getRuleShortName()] += 1;
        }

        asort($counts);

        return array_reverse($counts);
    }
}
