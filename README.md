Install
====

Detailed setup blog post https://inevitabletech.uk/blog/static-analysis-for-your-bdd-scripts/

Quick Installation:

```
composer global require forceedge01/bdd-analyser --dev
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
