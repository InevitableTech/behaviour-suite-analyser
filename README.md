Introduction
====

Perform static analysis on your cucumber/gherkin styles bdd scripts and remediate issues that will prolong the life your test suite.

This tool is meant to be language agnostic (even though written in PHP7) and expected to work with any cucumber/gherkin style files.

Here is an example of a basic run:

![Run](https://raw.githubusercontent.com/forceedge01/behaviour-suite-analyser/master/extras/bdd-analyser.png#version=1)

Html report:

![Run](https://raw.githubusercontent.com/forceedge01/behaviour-suite-analyser/master/extras/report.png#version=1)

- Summary of analysis.
- Segmented data view.
- Track your fixes as you resolve them.

Install
====

Detailed setup blog post https://inevitabletech.uk/blog/static-analysis-for-your-bdd-scripts/

Quick Installation:

```
composer global require forceedge01/bdd-analyser
```

Initialise config file to root of project, setup includes the kind of files you want to scan and the rules you want applied.

Example run:

```bash
bdd-analyser init
bdd-analyser scan . --config=.
```

The above command will lint the features folder.

Configure the bdd-analyser-config.yaml file with the extension of the files that contain the cucumber/gherkin scripts.

```yaml
    feature_file_extension: feature
```

Major change
-----

- PHP version 8.0 compatible.

Development
-----

```
make install
```

Adding new rules is as simple as creating a new class and extending it from the BaseRule class which implements the necessary interface and abstraction.

```php

namespace MyApp\BddScriptRules;

use Forceedge01\BDDStaticAnalyserRules\Entities;
use Forceedge01\BDDStaticAnalyserRules\Rules;

class MyRule extends Rules\BaseRule {

}
```

Then simply add your new class to the bdd-analyser-config.yaml rules array.

```yaml
    ...
    rules:
        ...
        - MyApp\BddScriptRules\MyRule
    ...
```
