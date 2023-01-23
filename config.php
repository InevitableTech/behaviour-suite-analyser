<?php

use Forceedge01\BDDStaticAnalyser\Rules;

// Parent directory where all feature files are present.
$feature_file_extension = 'feature';
$step_definition_file_extension = 'php';

// Class => ?array
$rules = [
	Rules\NoUrlInSteps::class => null,
	Rules\NoLongScenarios::class => [10],
	// OnlyValidOrderAllowed::class => 'strict',
	// NoSelectorsInSenarios::class => 'strict',
	// OnlyFirstPersonLanguageAllowed::class => 'strict',
];
