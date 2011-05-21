#!/bin/bash
rm -r docs/ && phpdoc -o HTML:Smarty:default -t ./docs -d .
php makepackage.php make
pear package
echo 'This packaging script has not committed anything. Check the git status and commit changes you want to keep!'

