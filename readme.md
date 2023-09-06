# AseriosPHP <!-- omit in toc -->

[![pipeline status](https://gitlab.com/asteriosframework/core/badges/develop/pipeline.svg)](https://gitlab.com/asteriosframework/core/-/commits/develop)
[![coverage report](https://gitlab.com/asteriosframework/core/badges/develop/coverage.svg)](https://gitlab.com/asteriosframework/core/-/commits/develop)
[![Latest Release](https://gitlab.com/asteriosframework/core/-/badges/release.svg)](https://gitlab.com/asteriosframework/core/-/releases)

- [Installation](#installation)
- [Description](#description)
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
