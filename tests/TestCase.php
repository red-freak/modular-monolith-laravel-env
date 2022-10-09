<?php

namespace RedFreak\ModularEnv\Tests;

use Exception;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\URL;
use Orchestra\Testbench\TestCase as PackageTestCase;
use RedFreak\ModularEnv\Contracts\ModularEnvironmentApplication;
use RedFreak\ModularEnv\Foundation\Bootstrap\LoadEnvironmentVariables as RedFreakLoadEnvironmentVariables;
use RedFreak\ModularEnv\ModularEnvServiceProvider;
use ReflectionClass;
use ReflectionException;
use Storage;

class TestCase extends PackageTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ModularEnvServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        //
    }

    /**
     * Scans the file for the use of facades, which is forbidden in this context (config is not loaded, so the facades
     * do not work.
     *
     * @param class-string $classToCheck
     *
     * @throws ReflectionException
     */
    protected function checkForFacadeInheritance(string $classToCheck, array $allowedClasses = []): void
    {
        $useStatements = [];

        $reflection = new ReflectionClass($classToCheck);
        $tokens = token_get_all(file_get_contents($reflection->getFileName()));

        $tokenCount   = count($tokens);
        $currentToken = 0;
        do {
            if (!is_array($tokens[$currentToken])) {
                ++$currentToken;
                continue;
            }
            // if there is a T_USE-Token, we look at the FQN
            if ($currentToken + 2 < $tokenCount && is_array($tokens[$currentToken + 2]) && token_name($tokens[$currentToken][0]) === 'T_USE') {
                if (!in_array($tokens[$currentToken + 2][1], $allowedClasses, true)) {
                    $this->checkClassForFacadeInheritance($tokens[$currentToken + 2][1], $tokens[$currentToken + 2][2]);
                }

                // remember the use-statements
                if (token_name(data_get($tokens, ($currentToken + 4) . '.0')) === 'T_AS') {
                    $useStatements[] = data_get($tokens, ($currentToken + 5) . '.1');
                    $currentToken += 5;
                } else {
                    $useStatements[] = basename($tokens[$currentToken + 2][1]);
                    $currentToken += 3;
                }
            }

            if (!is_array($tokens[$currentToken])
                || in_array($tokens[$currentToken][0], [T_COMMENT, T_DOC_COMMENT, T_COMMENT, T_START_HEREDOC], true)
            ) {
                ++$currentToken;
                continue;
            }

            if ($currentToken + 1 < $tokenCount
                && is_array($tokens[$currentToken + 1]) && token_name($tokens[$currentToken+1][0]) === 'T_DOUBLE_COLON'
                && (token_name($tokens[$currentToken][0]) === 'T_NAME_FULLY_QUALIFIED' || token_name($tokens[$currentToken][0]) === 'T_STRING')
            ) {
                $className = $tokens[$currentToken][1];
                // is the className part of the use-statements, we checked the class
                if (in_array($className, $useStatements)) {
                    ++$currentToken;
                    continue;
                }

                if (! in_array($className, $allowedClasses, true)) {
                    $this->checkClassForFacadeInheritance($className, $tokens[$currentToken][2]);
                }
            }

            ++$currentToken;
        } while ($currentToken < $tokenCount);
    }

    /**
     * @param  string  $className
     * @param  int  $line
     *
     * @return void
     *
     */
    private function checkClassForFacadeInheritance(string $className, int $line): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/\\\\Facades\\\\/',
            $className,
            'The use of Facades is not allowed in this context ('.$line.': '.$className.').'
        );
        // check also the parent classes
        try {
            $reflectionOfUsedClass = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            return;
        }

        if ($reflectionOfUsedClass->isTrait()) {
            return;
        }
        try {
            while ($reflectionOfUsedClass = $reflectionOfUsedClass->getParentClass()) {
                $this->assertDoesNotMatchRegularExpression(
                    '/\\\\Facade/',
                    $reflectionOfUsedClass->getName(),
                    'The use of Facades is not allowed in this context ('.$line.': '.$className.').'
                );
            }
        } catch(ReflectionException $e) {
            // nil
        }
    }

    protected function createAppWithContract(?string $basePath, array $additionalPaths = []): ApplicationContract {
        return new class($basePath, $additionalPaths)  extends Application implements ModularEnvironmentApplication  {
            public function __construct($basePath = null, private readonly array $additionalPaths = [])
            {
                parent::__construct($basePath);
            }

            public function additionalEnvFiles(): array
            {
                return $this->additionalPaths;
            }
        };
    }

    /**
     * Replace the given disk with a local testing disk.
     *
     * @param  string|null  $disk
     * @param  array  $config
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function fakedFilesystem(?string $disk = null, array $config = []): \Illuminate\Contracts\Filesystem\Filesystem
    {
        $disk = $disk ?: Config::get('filesystems.default');

        $root = storage_path('framework'.DIRECTORY_SEPARATOR.'testing'.DIRECTORY_SEPARATOR.'disks'.DIRECTORY_SEPARATOR.$disk);

        if ($token = ParallelTesting::token()) {
            $root = "{$root}_test_{$token}";
        }

        (new Filesystem)->cleanDirectory($root);

        Storage::set($disk, $fake = Storage::createLocalDriver(array_merge($config, [
            'root' => $root,
        ])));

        return tap($fake)->buildTemporaryUrlsUsing(function ($path, $expiration) {
            return URL::to($path.'?expiration='.$expiration->getTimestamp());
        });
    }
}
