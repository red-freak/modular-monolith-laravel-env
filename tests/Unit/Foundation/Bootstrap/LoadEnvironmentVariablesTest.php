<?php

namespace RedFreak\ModularEnv\Tests\Unit\Foundation\Bootstrap;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables as IlluminateLoadEnvironmentVariables;
use Illuminate\Support\Facades\Storage;
use RedFreak\ModularEnv\Foundation\Bootstrap\LoadEnvironmentVariables as RedFreakLoadEnvironmentVariables;
use RedFreak\ModularEnv\Tests\TestCase;
use ReflectionException;

class
LoadEnvironmentVariablesTest extends TestCase
{
    private RedFreakLoadEnvironmentVariables $bootstrapper;
    
    public function setUp(): void
    {
        $this->bootstrapper = new RedFreakLoadEnvironmentVariables();
        parent::setUp();
    }


    public function test_01_inheritance(): void
    {
        $this->assertInstanceOf(IlluminateLoadEnvironmentVariables::class, $this->bootstrapper);
    }

    public function test_02_recognizing_directories(): void
    {
        // test without Contract and mocking "default" laravel-app
        $this->assertEquals([$this->app->environmentPath()], $this->bootstrapper->environmentPaths($this->app));

        // test without Contract and mocking the Filesystem and creating directories to find
        $storageFake = $this->fakedFilesystem();
        $storageRoot = data_get(Storage::getConfig(), 'root');
        $this->app->setBasePath($storageRoot);
        $storageFake->makeDirectory('src'.DIRECTORY_SEPARATOR.'ModuleOne');
        $this->assertDirectoryExists($storageRoot.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'ModuleOne');
        $storageFake->makeDirectory('src'.DIRECTORY_SEPARATOR.'ModuleTwo');
        $this->assertDirectoryExists($storageRoot.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'ModuleTwo');
        $storageFake->put('test-file', 'fake content');
        $this->assertFileExists($storageRoot.DIRECTORY_SEPARATOR.'test-file');
        $expectedDirectories = [
            $this->app->environmentPath(),
            $this->app->basePath('src'.DIRECTORY_SEPARATOR.'ModuleOne'.DIRECTORY_SEPARATOR),
            $this->app->basePath('src'.DIRECTORY_SEPARATOR.'ModuleTwo'.DIRECTORY_SEPARATOR)
        ];
        $this->assertEquals($expectedDirectories, $this->bootstrapper->environmentPaths($this->app));

        // test with Contract and same mocked fs, but without additional files
        $appWithInterface = $this->createAppWithContract($this->app->basePath());
        $this->assertEquals([$this->app->environmentPath()], $this->bootstrapper->environmentPaths($appWithInterface));

        // test with Contract and no pathes to find
        $appWithInterface = $this->createAppWithContract($this->app->basePath(), [DIRECTORY_SEPARATOR.'no_src'.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR]);
        $this->assertEquals([$this->app->environmentPath()], $this->bootstrapper->environmentPaths($appWithInterface));

        // test with Contract and no pathes to find within the path
        $appWithInterface = $this->createAppWithContract($this->app->basePath(), [DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'ModuleOne'.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR]);
        $this->assertEquals([$this->app->environmentPath()], $this->bootstrapper->environmentPaths($appWithInterface));

        // test with Contract and same mocked fs, with '/src/**/'
        $appWithInterface = $this->createAppWithContract($this->app->basePath(), [DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR]);
        $this->assertEquals($expectedDirectories, $this->bootstrapper->environmentPaths($appWithInterface));

        // test with Contract and same mocked fs, add folder and test also in segment wildcard
        $storageFake->makeDirectory('other_src'.DIRECTORY_SEPARATOR.'ModuleThree');
        $appWithInterface = $this->createAppWithContract($this->app->basePath(), [DIRECTORY_SEPARATOR.'*src'.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR]);
        $expectedDirectories = [
            $this->app->environmentPath(),
            $this->app->basePath('other_src'.DIRECTORY_SEPARATOR.'ModuleThree'.DIRECTORY_SEPARATOR),
            $this->app->basePath('src'.DIRECTORY_SEPARATOR.'ModuleOne'.DIRECTORY_SEPARATOR),
            $this->app->basePath('src'.DIRECTORY_SEPARATOR.'ModuleTwo'.DIRECTORY_SEPARATOR)
        ];
        $this->assertEquals($expectedDirectories, $this->bootstrapper->environmentPaths($appWithInterface));

        // test with Contract and same mocked fs, add folders and add path directly
        $storageFake->makeDirectory('other_src'.DIRECTORY_SEPARATOR.'ModuleThree');
        $appWithInterface = $this->createAppWithContract($this->app->basePath(), [
            DIRECTORY_SEPARATOR.'other_src'.DIRECTORY_SEPARATOR.'ModuleThree'.DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR
        ]);
        $this->assertEquals($expectedDirectories, $this->bootstrapper->environmentPaths($appWithInterface));

        // test with Contract and fresh mocked fs, add folders and add path directly
        (new Filesystem)->cleanDirectory(data_get(Storage::getConfig(), 'root'));
        $this->assertEquals([$this->app->environmentPath()], $this->bootstrapper->environmentPaths($appWithInterface));
    }

    /**
     * @throws ReflectionException
     */
    public function test_03_no_usage_of_facades(): void
    {
        $this->checkForFacadeInheritance(RedFreakLoadEnvironmentVariables::class);
    }
}
