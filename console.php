<?php

// 1. Load folder and find files of type .feature
// 2. Store each step used
// 3. Store scenarios with titles and line number.
// 5. Define hooks.
// 6. Check background length.
// 7. Send report to live server, show local report.



// Rules 
// 3. Each step should refer to first person
// 4. The total length of a scenario should not exceed X
// 6. Stats on files and folders.
// 7. Map how the website looks like based on the structure of the files and folders.
// 8. Extract number of pages contained and form a hierarchy of the web application.
// 8. Stats on real time execution? Not now.
// 9. Scenario should be in Given, When, And, Then, But order.
// 10. Scenario must have atleast one Then statement.
// 11. Language should be ubiquitous.
// 12. Step definition should not execute complex statements.
// 13. Defined step definitions should be used atleast once.

use Forceedge01\BDDStaticAnalyser\Processor;
use Forceedge01\BDDStaticAnalyser\Entities;

//Config
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

function isFeatureFile($file) {
	if (is_file($file) && has_extension($file, $feature_file_extension)) {
		return true;
	}
}

function getAllFeatureFiles(string $directory, string $feature_file_extension) {
    $files = scandir($directory);
    $features = [];
    foreach ($files as $file) {
        if (is_dir($file) && ($file != '.' && $file != '..')) {
        	echo $file;
        	echo $directory . DIRECTORY_SEPARATOR . $file;
        	exit;
        	$featrues[] = getAllFeatureFiles($directory . DIRECTORY_SEPARATOR . $file);
        }

        if (strpos($file, '.' . $feature_file_extension) !== false) {
            $features[] = $directory . DIRECTORY_SEPARATOR . $file;
        }
    }

    return $features;
}

echo '<===== Behaviour Automation Suite Analayser =====>' . PHP_EOL . PHP_EOL;

$path = $argv[1];
$dir_to_scan = realpath($path);

try {

    if (! $dir_to_scan || ! is_dir($dir_to_scan)) {
        throw new Exception("First param (provided: '$path') must point to the folder where feature files are stored.");
    }

    $rulesProcessor = new Processor\RulesProcessor($rules);
    $files = getAllFeatureFiles($dir_to_scan, $feature_file_extension);
    $outcomeCollection = new Entities\OutcomeCollection();

    foreach ($files as $file) {
    	$outcomes[] = $rulesProcessor->applyRules($file, $outcomeCollection);
    }

    $displayProcessor = new Processor\DisplayProcessor();
    $displayProcessor->display($outcomeCollection);
} catch (\Exception $e) {
    echo '==> Error: ' . $e->getMessage() . PHP_EOL;
    exit;
}
