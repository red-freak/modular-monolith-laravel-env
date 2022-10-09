<?php

namespace RedFreak\ModularEnv\Tests\Unit\Dotenv\Store;

use Dotenv\Exception\InvalidPathException;
use Illuminate\Support\Env;
use RedFreak\ModularEnv\Dotenv\Dotenv;
use RedFreak\ModularEnv\Dotenv\Store\FileStore;
use RedFreak\ModularEnv\Tests\TestCase;
use Storage;

class FileStoreTest extends TestCase
{
    public function test_01_read(): void
    {
        $storageFake = $this->fakedFilesystem();
        $rootPath = data_get(Storage::getConfig(), 'root');
        $storageFake->put('.env', "HELLO=WORLD\n");
        // to set the default laravel path
        Dotenv::create(Env::getRepository(), null,null,false, null, $rootPath);
        // default .env (no prefix)
        $fileStore = new FileStore(
            [$rootPath.DIRECTORY_SEPARATOR.'.env'],
            true
        );
        $this->assertEquals("HELLO=WORLD\n", $fileStore->read());

        // additional .env but shortCircuit==true
        $storageFake->makeDirectory('src'.DIRECTORY_SEPARATOR.'ModuleOne');
        $storageFake->put('src'.DIRECTORY_SEPARATOR.'ModuleOne'.DIRECTORY_SEPARATOR.'.env', "HELLO=WORLD\n");
        $fileStore = new FileStore(
            [$rootPath.DIRECTORY_SEPARATOR.'.env', $rootPath.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'ModuleOne'.DIRECTORY_SEPARATOR.'.env'],
            true
        );
        $this->assertEquals("HELLO=WORLD\n", $fileStore->read());

        // additional .env but shortCircuit==false
        $fileStore = new FileStore(
            [$rootPath.DIRECTORY_SEPARATOR.'.env', $rootPath.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'ModuleOne'.DIRECTORY_SEPARATOR.'.env'],
            false
        );
        $this->assertMatchesRegularExpression('/HELLO=WORLD\\n/', $fileStore->read());
        $this->assertMatchesRegularExpression('/MODULE_ONE__HELLO=WORLD\\n/', $fileStore->read());
    }

    public function test_02_read_no_path(): void
    {
        $fileStore = new FileStore([], true);
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('At least one environment file path must be provided.');
        $fileStore->read();
    }

    public function test_02_read_no_env(): void
    {
        $fileStore = new FileStore([$this->app->basePath().DIRECTORY_SEPARATOR.'.no.env'], true);
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('Unable to read any of the environment file(s) at [' . $this->app->basePath().DIRECTORY_SEPARATOR.'.no.env].');
        $fileStore->read();
    }
}
