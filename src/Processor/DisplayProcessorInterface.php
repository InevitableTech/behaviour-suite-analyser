<?php

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyserRules\Entities;
use Symfony\Component\Console\Output\OutputInterface;

interface DisplayProcessorInterface {
    public function displayOutcomes(Entities\OutcomeCollection $outcomes, array $severities);
    public function printSummary(Entities\OutcomeCollection $outcomes, string $reportPath);
    public function inputSummary(string $path, string $severities, string $configPath, string $dirToScan);
}