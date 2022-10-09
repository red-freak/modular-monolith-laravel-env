<?php

namespace RedFreak\ModularEnv\Tests\Unit\Foundation\Bootstrap;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables as IlluminateLoadEnvironmentVariables;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\URL;
use RedFreak\ModularEnv\Contracts\ModularEnvironmentApplication;
use RedFreak\ModularEnv\Foundation\Bootstrap\LoadEnvironmentVariables as RedFreakLoadEnvironmentVariables;
use RedFreak\ModularEnv\Tests\TestCase;
use ReflectionClass;
use ReflectionException;
use Storage;
use Str;

class LoadEnvironmentVariablesTest extends TestCase
{
    public function test_01_inheritance(): void
    {
        $bootstrapper = new RedFreakLoadEnvironmentVariables();

        $this->assertInstanceOf(IlluminateLoadEnvironmentVariables::class, $bootstrapper);
    }

    public function test_02_recognizing_directories(): void
    {
        $bootstrapper = new RedFreakLoadEnvironmentVariables();

        // test without Contract and mocking "default" laravel-app
//        $this->assertEquals([$this->app->environmentPath()], $bootstrapper->environmentPaths($this->app));

        // test without Contract and mocking the Filesystem and creating directories to find
        $storageFake = $this->fakedFilesystem();
        $this->app->setBasePath(data_get(Storage::getConfig(), 'root'));
        $storageFake->makeDirectory('src'.DIRECTORY_SEPARATOR.'ModuleOne');
        $storageFake->makeDirectory('src'.DIRECTORY_SEPARATOR.'ModuleTwo');
        $storageFake->put('test-file', 'fake content');
        $expectedDirectories = [
            $this->app->environmentPath(),
            $this->app->basePath('src'.DIRECTORY_SEPARATOR.'ModuleOne'.DIRECTORY_SEPARATOR),
            $this->app->basePath('src'.DIRECTORY_SEPARATOR.'ModuleTwo'.DIRECTORY_SEPARATOR)
        ];
        $this->assertEquals($expectedDirectories, $bootstrapper->environmentPaths($this->app));

        // test with Contract and same mocked fs, but without additional files
        $appWithInterface = $this->createAppWithContract($this->app->basePath());
        $this->assertEquals([$this->app->environmentPath()], $bootstrapper->environmentPaths($appWithInterface));

        // test with Contract and no pathes to find
        $appWithInterface = $this->createAppWithContract($this->app->basePath(), [DIRECTORY_SEPARATOR.'no_src'.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR]);
        $this->assertEquals([$this->app->environmentPath()], $bootstrapper->environmentPaths($appWithInterface));

        // test with Contract and no pathes to find within the path
        $appWithInterface = $this->createAppWithContract($this->app->basePath(), [DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'ModuleOne'.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR]);
        $this->assertEquals([$this->app->environmentPath()], $bootstrapper->environmentPaths($appWithInterface));

        // test with Contract and same mocked fs, with '/src/**/'
        $appWithInterface = $this->createAppWithContract($this->app->basePath(), [DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR]);
        $this->assertEquals($expectedDirectories, $bootstrapper->environmentPaths($appWithInterface));

        // test with Contract and same mocked fs, add folder and test also in segment wildcard
        $storageFake->makeDirectory('other_src'.DIRECTORY_SEPARATOR.'ModuleThree');
        $appWithInterface = $this->createAppWithContract($this->app->basePath(), [DIRECTORY_SEPARATOR.'*src'.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR]);
        $expectedDirectories = [
            $this->app->environmentPath(),
            $this->app->basePath('other_src'.DIRECTORY_SEPARATOR.'ModuleThree'.DIRECTORY_SEPARATOR),
            $this->app->basePath('src'.DIRECTORY_SEPARATOR.'ModuleOne'.DIRECTORY_SEPARATOR),
            $this->app->basePath('src'.DIRECTORY_SEPARATOR.'ModuleTwo'.DIRECTORY_SEPARATOR)
        ];
        $this->assertEquals($expectedDirectories, $bootstrapper->environmentPaths($appWithInterface));

        // test with Contract and same mocked fs, add folders and add path directly
        $storageFake->makeDirectory('other_src'.DIRECTORY_SEPARATOR.'ModuleThree');
        $appWithInterface = $this->createAppWithContract($this->app->basePath(), [
            DIRECTORY_SEPARATOR.'other_src'.DIRECTORY_SEPARATOR.'ModuleThree'.DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR
        ]);
        $this->assertEquals($expectedDirectories, $bootstrapper->environmentPaths($appWithInterface));

        // test with Contract and fresh mocked fs, add folders and add path directly
        (new Filesystem)->cleanDirectory(data_get(Storage::getConfig(), 'root'));
        $this->assertEquals([$this->app->environmentPath()], $bootstrapper->environmentPaths($appWithInterface));
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
     * Scans the file for the use of facades, which is forbidden in this context (config is not loaded, so the facades
     * do not work.
     */
    public function test_03_no_usage_of_facades(): void
    {
        $useStatements = [];

        $reflection = new ReflectionClass(RedFreakLoadEnvironmentVariables::class);
        $tokens = token_get_all(file_get_contents($reflection->getFileName()));

        $tokenCount   = count($tokens);
        $currentToken = 0;
        $continue     = true;
        do {
            if (!is_array($tokens[$currentToken])) {
                ++$currentToken;
                continue;
            }
            // if there is a T_USE-Token, we look at the FQN
            if (token_name($tokens[$currentToken][0]) === 'T_USE') {
                $this->checkClassForFacadeInheritance($tokens[$currentToken + 2][1], $tokens[$currentToken + 2][2]);

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

                $this->checkClassForFacadeInheritance($className, $tokens[$currentToken][2]);
            }

            ++$currentToken;
        } while ($continue && $currentToken < $tokenCount);
    }

    /**
     * @param  string  $className
     *
     * @return void
     * @throws ReflectionException
     */
    private function checkClassForFacadeInheritance(string $className, int $line): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/\\\\Facades\\\\/',
            $className,
            'The use of Facades is not allowed in this context ('.$line.': '.$className.').'
        );
        // check also the parent classes
        $reflectionOfUsedClass = new ReflectionClass($className);
        while ($reflectionOfUsedClass = $reflectionOfUsedClass->getParentClass()) {
            $this->assertDoesNotMatchRegularExpression(
                '/\\\\Facade/',
                $reflectionOfUsedClass->getName(),
                'The use of Facades is not allowed in this context ('.$line.': '.$className.').'
            );
        }
    }
}
