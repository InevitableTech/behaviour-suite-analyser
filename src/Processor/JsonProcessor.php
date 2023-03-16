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
        $activeRules = ArrayProcessor::cleanArray($outcomeCollection->getSummary('activeRules'));
        $activeSteps = ArrayProcessor::cleanArray($outcomeCollection->getSummary('activeSteps'));
        unset($outcomeCollection->summary['activeRules']);
        unset($outcomeCollection->summary['activeSteps']);

        $output = [
            'severities' => $severities,
            'summary' => $outcomeCollection->summary,
            'active_steps' => $activeSteps,
            'active_rules' => $activeRules,
            'violations' => $outcomeCollection->getItems()
        ];

        $json = json_encode($output);

        file_put_contents($reportPath, $json);

        return $reportPath;
    }
}
