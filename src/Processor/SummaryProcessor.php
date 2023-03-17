<?php

declare(strict_types=1);

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyserRules\Entities;

class SummaryProcessor
{
    public static function setSummary(Entities\OutcomeCollection $outcomeCollection)
    {
        $summary = $outcomeCollection->summary;
        $summary['violationsCount'] = count($outcomeCollection->getItems());
        $summary['scenariosCount'] = count($outcomeCollection->summary['scenarios']);
        $summary['activeStepsCount'] = count($outcomeCollection->summary['activeSteps']);
        $summary['activeRulesCount'] = count($outcomeCollection->summary['activeRules']);
        $summary['backgroundsCount'] = count($outcomeCollection->getSummary('backgrounds'));
        $summary['tagsCount'] = count($outcomeCollection->getSummary('tags'));

        $outcomeCollection->summary = $summary;
    }
}
