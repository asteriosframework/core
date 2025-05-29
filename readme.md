<p align="center">
  <a href="" rel="noopener">
 <img width=200px height=88px src="./docs/asteriosphp-logo.png" alt="AsteriosPHP"></a>
</p>

<div align="center">

[![Status](https://img.shields.io/badge/status-active-success.svg)]()
![Maintainer](https://img.shields.io/badge/maintainer-@asteriosphp-blue)
![Maintainer](https://img.shields.io/badge/maintainer-@jfheinrich-blue)
[![GitHub Issues](https://img.shields.io/github/issues/asteriosframework/core.svg)](https://github.com/asteriosframework/core/issues)
[![GitHub pull-requests](https://img.shields.io/github/issues-pr/asteriosframework/core.svg)](https://GitHub.com/Naereen/StrapDown.js/pull/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](/LICENSE)

</div>


<p align="center">The AsteriosPHP Framework is a simple and flexible PHP 8.+ framework, inspired from the best features of other frameworks, in a modern and elegant way.
    <br>
</p>

<h2>Table of Contents</h2>

- [Installation](#installation)
- [Description](#description)
- [PHP CS Fixer](#php-cs-fixer)
- [Credits](#credits)


## Installation

This framework is ready to use it with [ddev](https://ddev.readthedocs.io/en/stable/)
or with VScode DevContainer.

*Repository installtion*

```bash
$ git clone git@github.com:asteriosframework/core.git
```

*Composer installation*

```bash
$ composer require asterios/core
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
