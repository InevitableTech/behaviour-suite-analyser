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


Development
-----

```
make install
```

Adding new rules is as simple as creating a new class and extending it from the BaseRule class which implements the necessary interface and abstraction.

```php

namespace Forceedge01\BDDStaticAnalyser\Rules;

use Forceedge01\BDDStaticAnalyser\Entities;

class MyRule extends BaseRule {

}
```

Then simply add your new class to the config.php rules array.
