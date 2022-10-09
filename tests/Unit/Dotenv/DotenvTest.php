<?php

namespace RedFreak\ModularEnv\Tests\Unit\Dotenv;

use Dotenv\Dotenv as DotenvDotenv;
use Dotenv\Store\FileStore as DotenvFileStore;
use Illuminate\Support\Env;
use RedFreak\ModularEnv\Dotenv\Dotenv as RedFreakDotenv;
use RedFreak\ModularEnv\Dotenv\Store\FileStore as RedFreakFileStore;
use RedFreak\ModularEnv\Tests\TestCase;
use ReflectionClass;

class DotenvTest extends TestCase
{
    public function test_01_create(): void
    {
        $redFreakDotenv = RedFreakDotenv::create(Env::getRepository(), ['test/'], ['.env'],true, 'test', '.env');
        $dotenvDotenv = DotenvDotenv::create(Env::getRepository(), ['test/'], ['.env'],true, 'test', '.env');
        $dotenvReflection = new ReflectionClass(DotenvDotenv::class);
        $this->assertEquals('.env', RedFreakDotenv::laravelDefaultPath());

        $redFreakFileStore = $dotenvReflection->getProperty('store')->getValue($redFreakDotenv);
        $dotenvFileStore = $dotenvReflection->getProperty('store')->getValue($dotenvDotenv);
        $this->assertEquals(RedFreakFileStore::class, $redFreakFileStore::class);
        $this->assertEquals(DotenvFileStore::class, $dotenvFileStore::class);
        $redFreakFileStoreReflection = new ReflectionClass(RedFreakFileStore::class);
        $dotenvFileStoreReflection = new ReflectionClass(DotenvFileStore::class);
        $this->assertEquals(['test/\.env'], $redFreakFileStoreReflection->getProperty('filePaths')->getValue($redFreakFileStore));
        $this->assertEquals($dotenvFileStoreReflection->getProperty('filePaths')->getValue($dotenvFileStore), $redFreakFileStoreReflection->getProperty('filePaths')->getValue($redFreakFileStore));
        $this->assertEquals(true, $redFreakFileStoreReflection->getProperty('shortCircuit')->getValue($redFreakFileStore));
        $this->assertEquals($dotenvFileStoreReflection->getProperty('shortCircuit')->getValue($dotenvFileStore), $redFreakFileStoreReflection->getProperty('shortCircuit')->getValue($redFreakFileStore));
        $this->assertEquals('test', $redFreakFileStoreReflection->getProperty('fileEncoding')->getValue($redFreakFileStore));
        $this->assertEquals($dotenvFileStoreReflection->getProperty('fileEncoding')->getValue($dotenvFileStore), $redFreakFileStoreReflection->getProperty('fileEncoding')->getValue($redFreakFileStore));

        $this->assertEquals($dotenvReflection->getProperty('parser')->getValue($redFreakDotenv)::class, $dotenvReflection->getProperty('parser')->getValue($dotenvDotenv)::class);
        $this->assertEquals($dotenvReflection->getProperty('loader')->getValue($redFreakDotenv)::class, $dotenvReflection->getProperty('loader')->getValue($dotenvDotenv)::class);
        $this->assertEquals($dotenvReflection->getProperty('repository')->getValue($redFreakDotenv)::class, $dotenvReflection->getProperty('repository')->getValue($dotenvDotenv)::class);
    }
}
