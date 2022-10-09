> **Warning**
> This Package is still work in progress!

> **Warning**
> The package is basically functional, but there is no logic to handle the files in a repo.

# Modular Monolith Laravel Env
The task of this package is to manage module dotnev-files used by the Laravel integration of [phpdotenv](https://github.com/vlucas/phpdotenv).
To store these files in source control without populating its contents to tjhe public the package is also based on Laravels [environment encrypting](https://laravel.com/docs/9.x/configuration#encrypting-environment-files) by a shared secret. This way the package can also be used for [forge](https://forge.laravel.com/) or [envoyer](https://envoyer.io/) deployments.  

* This package is inspired by the [Laracon Online Winter '22 talk](https://youtu.be/0Rq-yHAwYjQ?t=4129) by [Ryuta Hamasaki](https://github.com/avosalmon): "Modularising the Monolith" ([slides](https://speakerdeck.com/avosalmon/modularising-the-monolith-laracon-online-winter-2022) | [gitHub](https://github.com/avosalmon/modular-monolith-laravel)) and though of as an extension for such approaches.
* 

## Systemrequirements
* This package is only tested with `Laravel 9`. So it requires `"vlucas/phpdotenv": "^5.4.1"`.
* The package is written in `PHP 8.1`.

## Installation

To install you have to replace the Kernel-classes of Laravel.

#### app/Http/Kernel.php

```php
// replace
5: use Illuminate\Foundation\Http\Kernel as HttpKernel;
```
```php
// with
5: use RedFreak\ModularEnv\Foundation\Http\Kernel as HttpKernel;
```

#### app/Console/Kernel.php
```php
// replace
6: use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
```
```php
// with
6: use RedFreak\ModularEnv\Foundation\Console\Kernel as ConsoleKernel;
```

## Testing

run in develope
```sh
./vendor/bin/phpunit
```

## How to use

### getting started
The package assumes that the including package is structured following the modular monolith talk with the additional dotenv-files by this logic:
```bash
.
├── app
├── .env
├── ...
├── src
│    ├── ModuleOne
│    │    ├── ...
│    │    └── .env
│    ├── ...
│    └── ModuleLast
│         ├── ...
│         └── .env
└── ...
```
If your structure is different you have to implement the `ModularEnvironmentApplication`-Contract into your application. By the method `ModularEnvironmentApplication::additionalEnvFiles()` the package will recognize different paths (* and ** are allowed and used like in the .gitignore).

### using the variables
Variables form the default `.env` are accessed like before with the `env()`-helper.

Variables from the modules are accessed the same way with the difference that they are prefixed with the module name in SNAKE_CASE (caps) and `__`. E.g. the Variable `HELLO=WORLD` from the file `/src/FooBar/.env` can be accessed by `env('FOO_BAR__HELLO')`.

## tasks
### tasks for 0.3.0 - encrypting the files
- [ ] support for `env:encrypt` and `env:decrypt` to handle the files
- [ ] implementing `modular_env:encrypt` and `modular_env:deecrypt` to support deployment processes
### 1.0.0-alpha
- [ ] maybe writing additional tests
- [ ] adding a test-pipeline for gitHub
- [ ] fixing and testing of 0.3.0 in real-life-environments
### 1.0.0 - MVP
- [ ] fixed release of 1.0.0-alpha
### 1.1.0 - LaravelRay
- [ ] support for LaravelRay

## recommendations

#### use the following in your `.gitignore`
```bash
.env
.env.*
!.env.example
!.env*.encrypted
!.env.pipelines
/src/**/.env
/src/**/.env.*
!/src/**/.env*.encrypted
!/src/**/.env.example
```
## Why do I?

### Why do I have to replace Kernel-Classes?
I see, replacing the Kernel is a big issue. But .env-files are bootstrapped before anything else, so we have to hook something before everything else. The Kernels are present and inheriting, so it's a good point to hook in.

### Need to create my own Application class?
You don't have to it's just a possibility to determine a custom path-structure on your dotenv-files. The default "setting" will be to guess files in `/src/**/`.

### Need to create my own Application class if I have a custom path-structure on my .env-files?
The problem for this package is, that we want to hook a behaviour which is deep in the functionality of Laravel. At the moment the dotenv-files are loaded there is just a "stub" of the `Application`, there is no way to use `config` or `env`. So this seemed the best option to not produce unnecessary disk-requests. This is especially important for applications handling hundreds of requests per second.

## Licence
This package is free to use as stated by the [LICENCE.md](LICENSE.md) under the MIT License, but you can [buy me a coffee](https://www.buymeacoffee.com/redFreak) if you want :D.
