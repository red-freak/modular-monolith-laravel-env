# Modular Monolith Laravel Env
The task of this package is to manage module dotnev-files used by Laravels integration of [phpdotenv](https://github.com/vlucas/phpdotenv).
To store these files in source control without populating it's contents to tjhe public the package is also based on Laravels [environment encrypting](https://laravel.com/docs/9.x/configuration#encrypting-environment-files) by a shared secret. This way the package can also be used for [forge](https://forge.laravel.com/) or [envoyer](https://envoyer.io/) deployments.  

* This package is inspired by the [Laracon Online Winter '22 talk](https://youtu.be/0Rq-yHAwYjQ?t=4129) by [Ryuta Hamasaki](https://github.com/avosalmon): "Modularising the Monolith" ([slides](https://speakerdeck.com/avosalmon/modularising-the-monolith-laracon-online-winter-2022) | [github](https://github.com/avosalmon/modular-monolith-laravel)) and though of as an extension for such approaches.

## Systemrequirements
* This package is only tested with `Laravel 9`. So it requires `"vlucas/phpdotenv": "^5.4.1"`.
* The package is written in `PHP 8.1`.

## Installation

## Testing

## How to use

## Licence ##
This package is free to use as stated by the [LICENCE.md](LICENSE.md) under the MIT License, but you can [buy me a coffee](https://www.buymeacoffee.com/redFreak) if you want :D.
