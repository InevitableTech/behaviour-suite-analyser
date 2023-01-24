<?php

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyser\Entities;

interface DisplayProcessorInterface {
    public function displayOutcomes(Entities\OutcomeCollection $outcomes, array $severities);
    public function printSummary(Entities\OutcomeCollection $outcomes);
    public function inputSummary(string $path, string $severities, string $configPath, string $dirToScan);
}