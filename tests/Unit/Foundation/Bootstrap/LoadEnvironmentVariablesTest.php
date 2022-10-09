<?php

namespace RedFreak\ModularEnv\Tests\Unit\Foundation\Bootstrap;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables as IlluminateLoadEnvironmentVariables;
use RedFreak\ModularEnv\Foundation\Bootstrap\LoadEnvironmentVariables as RedFreakLoadEnvironmentVariables;
use RedFreak\ModularEnv\Tests\TestCase;
use ReflectionException;
use Storage;

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
        $this->assertEquals([$this->app->environmentPath()], $bootstrapper->environmentPaths($this->app));

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
     * @throws ReflectionException
     */
    public function test_03_no_usage_of_facades(): void
    {
        $this->checkForFacadeInheritance(RedFreakLoadEnvironmentVariables::class);
    }
}
