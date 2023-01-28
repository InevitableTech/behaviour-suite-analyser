<?php

use Forceedge01\BDDStaticAnalyser\Rules;

return [
    // Parent directory where all feature files are present.
    'feature_file_extension' => 'feature',
    'step_definition_file_extension' => 'php',

    // Configure which class will process the outcomes and display the summary.
    'display_processor' => Forceedge01\BDDStaticAnalyser\Processor\DisplayProcessor::class,
    'report_processor' => Forceedge01\BDDStaticAnalyser\Processor\ReportProcessor::class,
    'html_report_path' => __DIR__ . '/build/report.html',

    // Class => ?array
    'rules' => [
        Rules\NoUrlInSteps::class => null,
        Rules\NoLongScenarios::class => [10],
        Rules\NoCommentedOutSteps::class => null,
        // Rules\UnsupportedTags::class => ['@dev', '@wip'],
        Rules\DiscouragedTags::class => null,
        Rules\OnlyValidOrderAllowed::class => null,
        Rules\NoSelectorsInSteps::class => null,
        Rules\OnlyFirstPersonLanguageAllowed::class => ['given', 'when', 'then', 'and', 'but'],
        Rules\NotTooManyScenariosPerFeature::class => [1],
        Rules\NotTooManyExamplesPerScenario::class => [3],
        // Rules\NoDuplicateScenarios => null,
        // Rules\NoScenarioWithoutDescription => null,
        // Rules\NoFeatureWithoutNarrative => null,
        // Rules\AllStepsInline => null,
        // Rules\AllScenariosInline => null,
        // Rules\NoSmallScenarios => [2],
        // Rules\NoEmptyFeature => null,
        // Rules\NoHiddenMultiSteps => null,
        // Rules\NoScenarioWithoutAssertion => null,
    ]
];
