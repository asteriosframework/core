# AseriosPHP <!-- omit in toc -->

- [Installation](#installation)
- [Description](#description)
- [**athene** the commandline tool](#athene-the-commandline-tool)
- [PHP CS Fixer](#php-cs-fixer)
- [Credits](#credits)


## Installation

This framework is ready to use it with [ddev](https://ddev.readthedocs.io/en/stable/).

*Repository installtion*

```bash
$ git clone git@gitlab.com:asteriosframework/core.git
```

*Composer installation*

```bash
$ composer require asterios/core
```

*Post install*

```bash
$ cd tools/php-cs-fixer
tools/php-cs-fixer$ composer install
```

## Description
AsteriosPHP is a lightweight PHP 8.x framework.

AsteriosPHP is fully PHP 8.1 compatible.

## **athene** the commandline tool

The commandline tool `athene` is main tool for maintenance, create database schemas/entities and information requests.

```bash
$ ./athene list
Doctrine Command Line Interface 2.15.2.0

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  completion                         Dump the shell completion script
  help                               Display help for a command
  list                               List commands
 dbal
  dbal:reserved-words                Checks if the current database contains identifiers that are reserved.
  dbal:run-sql                       Executes arbitrary SQL directly from the command line.
 orm
  orm:clear-cache:metadata           Clear all metadata cache of the various cache drivers
  orm:clear-cache:query              Clear all query cache of the various cache drivers
  orm:clear-cache:region:collection  Clear a second-level cache collection region
  orm:clear-cache:region:entity      Clear a second-level cache entity region
  orm:clear-cache:region:query       Clear a second-level cache query region
  orm:clear-cache:result             Clear all result cache of the various cache drivers
  orm:convert-d1-schema              [orm:convert:d1-schema] Converts Doctrine 1.x schema into a Doctrine 2.x schema
  orm:convert-mapping                [orm:convert:mapping] Convert mapping information between supported formats
  orm:ensure-production-settings     Verify that Doctrine is properly configured for a production environment
  orm:generate-entities              [orm:generate:entities] Generate entity classes and method stubs from your mapping information
  orm:generate-proxies               [orm:generate:proxies] Generates proxy classes for entity classes
  orm:generate-repositories          [orm:generate:repositories] Generate repository classes from your mapping information
  orm:info                           Show basic information about all mapped entities
  orm:mapping:describe               Display information about mapped objects
  orm:run-dql                        Executes arbitrary DQL directly from the command line
  orm:schema-tool:create             Processes the schema and either create it directly on EntityManager Storage Connection or generate the SQL output
  orm:schema-tool:drop               Drop the complete database schema of EntityManager Storage Connection or generate the corresponding SQL output
  orm:schema-tool:update             Executes (or dumps) the SQL needed to update the database schema to match the current mapping metadata
  orm:validate-schema                Validate the mapping files
```

## PHP CS Fixer

For code quality wie use the php-cs-fixer
tool, with following configuration:

`php-cs-fixer`

```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src');

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    'full_opening_tag' => true,
    'single_blank_line_at_eof' => false,
    'blank_line_after_opening_tag' => false,
    'curly_braces_position' => [
        'control_structures_opening_brace' => 'next_line_unless_newline_at_signature_end',
    ],
    'control_structure_continuation_position' => ['position' => 'next_line'],
])->setFinder($finder);
```

**Usage of PHP CS Fixer**

```bash
$ ./php-cs-fix -h
    PHP CS Fixer Tool

    Syntax: php-cs-fixer [-h|t|f]
    Options:
      h Print this help page
      t Run in dry-run mode
      f Fixes possible issues
$
```

## Credits

- [Ben Butschko](ben@asteriosphp.de)
- [Joerg Heinrich](joerg@asteriosphp.de)
