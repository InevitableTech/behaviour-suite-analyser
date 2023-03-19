<?php

declare(strict_types=1);

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyserRules\Entities;

class SummaryProcessor
{
    public static function setSummary(Entities\OutcomeCollection $outcomeCollection)
    {
        $filesWithViolations = array_unique(array_column($outcomeCollection->getItems(), 'file'));

        $summary = $outcomeCollection->summary;
        $summary['backgrounds'] = array_values($summary['backgrounds']);
        $summary['scenarios'] = array_values($summary['scenarios']);
        $summary['totalLinesCount'] = array_sum($outcomeCollection->summary['linesCount']);
        $summary['violationsCount'] = count($outcomeCollection->getItems());
        $summary['violatedFilesCount'] = count($filesWithViolations);
        $summary['scenariosCount'] = count($outcomeCollection->summary['scenarios']);
        $summary['activeStepsCount'] = count($outcomeCollection->summary['activeSteps']);
        $summary['activeRulesCount'] = count($outcomeCollection->summary['activeRules']);
        $summary['backgroundsCount'] = count($outcomeCollection->getSummary('backgrounds'));
        $summary['tagsCount'] = count($outcomeCollection->getSummary('tags'));

        $outcomeCollection->summary = $summary;
    }
}
