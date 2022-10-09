<?php

namespace RedFreak\ModularEnv\Dotenv;

use Dotenv\Dotenv as OrigDotenv;
use Dotenv\Loader\Loader;
use Dotenv\Parser\Parser;
use Dotenv\Repository\RepositoryInterface;
use RedFreak\ModularEnv\Dotenv\Store\StoreBuilder;

class Dotenv extends OrigDotenv
{
    private static ?string $laravelDefaultPath = null;

    public static function laravelDefaultPath() {
        return self::$laravelDefaultPath;
    }

    /** @iheritDoc */
    public static function create(
        RepositoryInterface $repository,
        $paths,
        $names = null,
        bool $shortCircuit = true,
        string $fileEncoding = null,
        $defaultPath = '',
    ) {
        // remember the laravel default path
        self::$laravelDefaultPath = $defaultPath;

        $builder = $names === null ? StoreBuilder::createWithDefaultName() : StoreBuilder::createWithNoNames();

        foreach ((array) $paths as $path) {
            $builder = $builder->addPath($path);
        }

        foreach ((array) $names as $name) {
            $builder = $builder->addName($name);
        }

        if ($shortCircuit) {
            $builder = $builder->shortCircuit();
        }

        return new self($builder->fileEncoding($fileEncoding)->make(), new Parser(), new Loader(), $repository);
    }
}
