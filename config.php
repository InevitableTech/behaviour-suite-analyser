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
		Rules\DiscouragedTags::class => null
		// OnlyValidOrderAllowed::class => 'strict',
		// NoSelectorsInSenarios::class => 'strict',
		// OnlyFirstPersonLanguageAllowed::class => 'strict',
	]
];
