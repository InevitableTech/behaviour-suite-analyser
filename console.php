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


//Config
require __DIR__ . '/config.php';

$folderContents = scandir($dir);

function is_feature_file($file) {
	if (is_file($file) && has_extension($file, $feature_file_extension)) {
		return true;
	}
}

$rulesProcessor = new RulesProcessor($rules);

foreach ($folderContents as $folderContent) {
	if (is_feature_file($folderContent)) {
		$outcomes[] = $rulesProcessessor->applyRules($folderContent);
	}
}

$displayProcessor = new DisplayProcessor();
$displayProcessor->display($outcomes);
