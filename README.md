Install

make install

Config in the Config.php file, setup includes the kind of files you want to scan and the rules you want to active.

Run the bin/console [folder] [?severities] file to kick things off. Example:

```bash
bin/console -d=features/
```

The above command will scan the features folder.
