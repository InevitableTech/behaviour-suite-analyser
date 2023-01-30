Description
====

Perform static analysis on your cucumber/gherkin styles bdd scripts and remediate issues that will prolong the life your test suite.

This tool is meant to be language agnostic (even though written in PHP7) and expected to work with any cucumber/gherkin style files.

Install
====

Detailed setup blog post https://inevitabletech.uk/blog/static-analysis-for-your-bdd-scripts/

Quick Installation:

```
composer global require forceedge01/bdd-analyser
```

Copy config.php file to root of project, setup includes the kind of files you want to scan and the rules you want applied.

Example run:

```bash
bdd-analyser -i
bdd-analyser -d=features/ -c=config.php
```

The above command will scan the features folder.

Configure the config.php file with the extension of the files that contain the cucumber/gherkin scripts.

```php
    'feature_file_extension' => 'feature',
```

Development
-----

```
make install
```

Adding new rules is as simple as creating a new class and extending it from the BaseRule class which implements the necessary interface and abstraction.

```php

namespace MyApp\BddScriptRules;

use Forceedge01\BDDStaticAnalyser\Entities;
use Forceedge01\BDDStaticAnalyser\Rules;

class MyRule extends Rules\BaseRule {

}
```

Then simply add your new class to the config.php rules array.

```php
    ...
    'rules' => [
        ...
        MyApp\BddScriptRules\MyRule::class => null,
        ...
    ]
    ...
```
