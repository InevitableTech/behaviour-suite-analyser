Install
====

```
composer require forceedge01/bdd-analyser --dev
```

Copy config.php file to root of project, setup includes the kind of files you want to scan and the rules you want applied.

Example run:

```bash
cp vendor/forceedge01/bdd-analyser/config.php .
vendor/bin/bdd-analyser -d=features/ -c=config.php
```

The above command will scan the features folder.


Development
-----

```
make install
```
