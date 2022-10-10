<?php

namespace RedFreak\ModularEnv\Tests\Unit\Dotenv\Store;

use RedFreak\ModularEnv\Dotenv\Store\FileStore;
use Dotenv\Store\FileStore as DotenvFileStore;
use RedFreak\ModularEnv\Dotenv\Store\StoreBuilder;
use Dotenv\Store\StoreBuilder as DotenvStoreBuilder;
use RedFreak\ModularEnv\Tests\TestCase;
use ReflectionClass;
use ReflectionException;

class StoreBuilderTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function test_01_creating(): void
    {
        $builderReflection = new ReflectionClass(StoreBuilder::class);
        $dotenvBuilderReflection = new ReflectionClass(DotenvStoreBuilder::class);

        // createWithNoNames
        $builder = StoreBuilder::createWithNoNames();
        $dotenvBuilder = DotenvStoreBuilder::createWithNoNames();
        $this->assertEquals(DotenvStoreBuilder::class, $dotenvBuilder::class);
        $this->assertEquals([], $builderReflection->getProperty('paths')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('paths')->getValue($dotenvBuilder), $builderReflection->getProperty('paths')->getValue($builder));
        $this->assertEquals([], $builderReflection->getProperty('names')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('names')->getValue($dotenvBuilder), $builderReflection->getProperty('names')->getValue($builder));
        $this->assertEquals(false, $builderReflection->getProperty('shortCircuit')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('shortCircuit')->getValue($dotenvBuilder), $builderReflection->getProperty('shortCircuit')->getValue($builder));
        $this->assertNull($builderReflection->getProperty('fileEncoding')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('fileEncoding')->getValue($dotenvBuilder), $builderReflection->getProperty('fileEncoding')->getValue($builder));

        // createWithDefaultName
        $builder = StoreBuilder::createWithDefaultName();
        $dotenvBuilder = DotenvStoreBuilder::createWithDefaultName();
        $this->assertEquals(DotenvStoreBuilder::class, $dotenvBuilder::class);
        $this->assertEquals([], $builderReflection->getProperty('paths')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('paths')->getValue($dotenvBuilder), $builderReflection->getProperty('paths')->getValue($builder));
        $this->assertEquals(['.env'], $builderReflection->getProperty('names')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('names')->getValue($dotenvBuilder), $builderReflection->getProperty('names')->getValue($builder));
        $this->assertEquals(false, $builderReflection->getProperty('shortCircuit')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('shortCircuit')->getValue($dotenvBuilder), $builderReflection->getProperty('shortCircuit')->getValue($builder));
        $this->assertNull($builderReflection->getProperty('fileEncoding')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('fileEncoding')->getValue($dotenvBuilder), $builderReflection->getProperty('fileEncoding')->getValue($builder));

        // addPath
        $builder = $builder->addPath('test');
        $dotenvBuilder = $dotenvBuilder->addPath('test');
        $this->assertEquals(DotenvStoreBuilder::class, $dotenvBuilder::class);
        $this->assertEquals(['test'], $builderReflection->getProperty('paths')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('paths')->getValue($dotenvBuilder), $builderReflection->getProperty('paths')->getValue($builder));
        $builder = $builder->addPath('test2');
        $dotenvBuilder = $dotenvBuilder->addPath('test2');
        $this->assertEquals(['test', 'test2'], $builderReflection->getProperty('paths')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('paths')->getValue($dotenvBuilder), $builderReflection->getProperty('paths')->getValue($builder));
        $this->assertEquals(['.env'], $builderReflection->getProperty('names')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('names')->getValue($dotenvBuilder), $builderReflection->getProperty('names')->getValue($builder));
        $this->assertEquals(false, $builderReflection->getProperty('shortCircuit')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('shortCircuit')->getValue($dotenvBuilder), $builderReflection->getProperty('shortCircuit')->getValue($builder));
        $this->assertNull($builderReflection->getProperty('fileEncoding')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('fileEncoding')->getValue($dotenvBuilder), $builderReflection->getProperty('fileEncoding')->getValue($builder));

        // addName
        $builder = $builder->addName('test');
        $dotenvBuilder = $dotenvBuilder->addName('test');
        $this->assertEquals(['test', 'test2'], $builderReflection->getProperty('paths')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('paths')->getValue($dotenvBuilder), $builderReflection->getProperty('paths')->getValue($builder));
        $this->assertEquals(['.env', 'test'], $builderReflection->getProperty('names')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('names')->getValue($dotenvBuilder), $builderReflection->getProperty('names')->getValue($builder));
        $this->assertEquals(false, $builderReflection->getProperty('shortCircuit')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('shortCircuit')->getValue($dotenvBuilder), $builderReflection->getProperty('shortCircuit')->getValue($builder));
        $this->assertNull($builderReflection->getProperty('fileEncoding')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('fileEncoding')->getValue($dotenvBuilder), $builderReflection->getProperty('fileEncoding')->getValue($builder));

        // shortCircuit
        $builder = $builder->shortCircuit();
        $dotenvBuilder = $dotenvBuilder->shortCircuit();
        $this->assertEquals(['test', 'test2'], $builderReflection->getProperty('paths')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('paths')->getValue($dotenvBuilder), $builderReflection->getProperty('paths')->getValue($builder));
        $this->assertEquals(['.env', 'test'], $builderReflection->getProperty('names')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('names')->getValue($dotenvBuilder), $builderReflection->getProperty('names')->getValue($builder));
        $this->assertEquals(true, $builderReflection->getProperty('shortCircuit')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('shortCircuit')->getValue($dotenvBuilder), $builderReflection->getProperty('shortCircuit')->getValue($builder));
        $this->assertNull($builderReflection->getProperty('fileEncoding')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('fileEncoding')->getValue($dotenvBuilder), $builderReflection->getProperty('fileEncoding')->getValue($builder));

        // fileEncoding
        $builder = $builder->fileEncoding('test');
        $dotenvBuilder = $dotenvBuilder->fileEncoding('test');
        $this->assertEquals(['test', 'test2'], $builderReflection->getProperty('paths')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('paths')->getValue($dotenvBuilder), $builderReflection->getProperty('paths')->getValue($builder));
        $this->assertEquals(['.env', 'test'], $builderReflection->getProperty('names')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('names')->getValue($dotenvBuilder), $builderReflection->getProperty('names')->getValue($builder));
        $this->assertEquals(true, $builderReflection->getProperty('shortCircuit')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('shortCircuit')->getValue($dotenvBuilder), $builderReflection->getProperty('shortCircuit')->getValue($builder));
        $this->assertEquals('test', $builderReflection->getProperty('fileEncoding')->getValue($builder));
        $this->assertEquals($dotenvBuilderReflection->getProperty('fileEncoding')->getValue($dotenvBuilder), $builderReflection->getProperty('fileEncoding')->getValue($builder));

        // make
        $fs = $builder->make();
        $this->assertInstanceOf(FileStore::class, $fs);
        $dotenvFs = $dotenvBuilder->make();
        $this->assertInstanceOf(DotenvFileStore::class, $dotenvFs);
        $fsReflection = new ReflectionClass($fs);
        $dotenvFsReflection = new ReflectionClass($dotenvFs);
        $this->assertEquals($dotenvFsReflection->getProperty('filePaths')->getValue($dotenvFs), $fsReflection->getProperty('filePaths')->getValue($fs));
        $this->assertEquals($dotenvFsReflection->getProperty('shortCircuit')->getValue($dotenvFs), $fsReflection->getProperty('shortCircuit')->getValue($fs));
        $this->assertEquals($dotenvFsReflection->getProperty('fileEncoding')->getValue($dotenvFs), $fsReflection->getProperty('fileEncoding')->getValue($fs));
    }
}
