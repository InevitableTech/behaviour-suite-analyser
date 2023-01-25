Install
====

composer require forceedge01/bdd-analyser --dev

Config in the Config.php file, setup includes the kind of files you want to scan and the rules you want to active.

Example run:

```bash
vendor/bin/bdd-analyser -d=features/ -c=config.php
```

The above command will scan the features folder.


Development
-----

```
make install
```
