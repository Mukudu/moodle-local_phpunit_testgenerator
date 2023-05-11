# Moodle PHPUnit Test Generator #
## moodle-local_phpunit_testgenerator ##

A developer tool to generate PHP Unit Test skeleton files from existing plugin code.  

Sub-plugins can be developed for special cases, please see documentation.

# Usage #

_php local/phpunit_testgenerator/cli/generateskeletons.php [--plugin-path=path/to/plugin] [--purge] [--help]_

**--plugin-path**     is required unless --help is specified and must exist.

**--purge**           overwrite existing test files - use with caution

**-h, --help**        print help


## TODOs ##

- Use getMockForTrait() for traits -  (how to test)

# See Also #

* https://phpunitgen.io/docs#/en/configuration 
* https://github.com/Idrinth/phpunit-test-generator
