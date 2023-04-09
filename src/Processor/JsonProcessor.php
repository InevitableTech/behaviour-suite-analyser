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
        $activeSteps = $outcomeCollection->getSummary('activeSteps');
        $tags = $outcomeCollection->summary['tags'];
        unset($outcomeCollection->summary['tags']);
        unset($outcomeCollection->summary['activeRules']);
        unset($outcomeCollection->summary['activeSteps']);

        $output = [
            'severities' => $severities,
            'summary' => $outcomeCollection->summary,
            'tags' => $tags,
            'active_steps' => $activeSteps,
            'active_rules' => $activeRules,
            'violations' => $outcomeCollection->getItems()
        ];

        $json = json_encode($output);

        file_put_contents($reportPath, $json);

        return $reportPath;
    }
}
