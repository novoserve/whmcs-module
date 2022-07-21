# Cloudrack Type Asset Tag

Data type for AssetTag. Supports the following formats:
```
123-123
NL-123-123
DEV-123-123
```

The prefixes are based on ISO-3166 country codes (except DEV):
https://en.wikipedia.org/wiki/ISO_3166

## Installation

The advised install method is through composer.

Because this package is not public, it will have to be added to the `repositories` section of your `composer.json` file: 

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:novoserve/cloudrack-type-assettag.git"
    }
  ]
}
```

After that it can be installed:
```sh
composer require novoserve/cloudrack-type-assettag
```

More detailed instruction for [adding AssetTags to existing repos](https://bookstack.novoserve.org/books/development-devops/page/adding-assettags-to-existing-repos) can be found in the Novoserve Knowledgebase.

PHP version 7.3 and higher is supported.

## Usage

This package contains a `ServerTag` class that can be used to validate and format asset tags for servers.

### Instantiation

It can be used by instantiating it with a valid tag string.

The created object can be used as a replacement for the current asset string:

```php
<?php

use NovoServe\Cloudrack\Types\ServerTag;

$tag = '123-456'; // or 'NL-123-456'

$server = new ServerTag($tag);

echo $server; // NL-123-456
```

If the given string does not have the correct Tag format, an `InvalidAssetTagException` will be thrown.

If the given Tag string does not have a supported location, an `InvalidAssetTagLocationException` will be thrown.

If a country-code prefix is used, it MUST be uppercase.

### Validation

A tag can be validated with the `validate()` method:

```php
<?php

$tag = '123-456'; // or 'NL-123-456'

$valid = NovoServe\Cloudrack\Types\ServerTag::isValid($tag); // true

$valid = NovoServe\Cloudrack\Types\ServerTag::isValid('123456'); // false
```

More examples are described in the dataprovider methods of the [test class](tests/tests/AssetTagTest.php).

## Development

Tests have been provided in the `tests/` directory.
After running `composer install`, these can be executed with PHPUnit:

```sh
vendor/bin/phpunit --config tests/phpunit.xml
```

Or with composer itself:
```sh 
composer test
```

## License

All rights reserved (c) 2022 NovoServe B.V.
