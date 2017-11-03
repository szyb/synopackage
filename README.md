# README #


### What is this repository for? ###

This is a PHP web-based application to search package for Synology DSM system from a third-party sources. This website contains a list of known package sources and allow user to find package basing on them DSM version and model.

### How do I get set up? ###
* Installation
    * clone the repository
    * install composer (if not installed already)
    * in terminal type: cd src && composer install

* Configuration
    * All configuration files are included in "conf" folder
* Dependencies
    * Mustache >= 2.5
    * symfony/yaml 3.3
    * monolog/monolog 1.23
    * autoload
    * PHP 7.1 (may be probably downgraded, however not tested)
    * phpunit/phpunit 6.3.0


* Database configuration
    * This is database less application
* How to run tests
    * call "phpunit" in main folder
* Deployment instructions
    * Copy all files from "src" folder except "test" in vendor folders
    * Create "cache" folder and make sure that it is writeable

### Contribution guidelines ###

* Writing tests
    * create "cache" folder in root directory (if not exists already) - it's mandatory for tests
    * run tests before develop feature
    * develop feature and add new tests
    * run tests after
    * if tests passed then commit
* Code review
    * I'm a single developer, so there is no code review (for now)
* Other guidelines
    * Just keep the current architecture

### Who do I talk to? ###

* Repo owner or admin
    * See my profile details
* Other community or team contact
    * None (for now)