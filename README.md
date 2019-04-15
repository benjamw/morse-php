morse-php
=========

PHP morse code utilities.

[![Build Status](https://travis-ci.org/skillcoder/morse-php.svg?branch=master)](https://travis-ci.org/skillcoder/morse-php)

# Usage

``` php
<?php
// Translate from/to morse:
$text = new Morse\Text();
$morse = $text->toMorse('SOS');

echo $morse; // ... --- ...
echo $text->fromMorse($morse); // SOS

// Generate a WAV-file:
$wav = new Morse\Wav();
file_put_contents('sos.wav', $wav->generate('SOS'));
```

~~~php
<?php
namespace Morse;
require_once('vendor/autoload.php');
$signal = '___ ._... _._  ._... __ ._... ._ ._... _.';
$table = new Table('_');
$text = new Text($table);
$text->setCaseSense(true);
$text->setUpperCaseMod(false);
echo $text->fromMorse($signal); // Ok man
~~~

# Installing

To include `morse-php` in your project, add it to your `composer.json` file:

```json
{
    "require": {
        "skillcoder/morse": "^1.0.0"
    }
}
```

# License

MIT licensed. See LICENSE for full terms.
