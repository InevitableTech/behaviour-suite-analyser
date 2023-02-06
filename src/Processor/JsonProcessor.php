<?php

declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyserRules\Entities;

class JsonProcessor implements ReportProcessorInterface
{
    public function generate(
        string $reportPath,
        array $severities,
        Entities\OutcomeCollection $outcomeCollection
    ): string {
        $outcomeCollection->severities = $severities;

        $json = json_encode($outcomeCollection);

        file_put_contents($reportPath, $json);

        return $reportPath;
    }
}
