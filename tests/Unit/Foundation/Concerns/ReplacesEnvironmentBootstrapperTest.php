<?php

namespace RedFreak\ModularEnv\Tests\Unit\Foundation\Concerns;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables as IlluminateLoadEnvironmentVariables;
use Illuminate\Foundation\Http\Kernel as IlluminateKernel;
use RedFreak\ModularEnv\Foundation\Bootstrap\LoadEnvironmentVariables as RedFreakLoadEnvironmentVariables;
use RedFreak\ModularEnv\Foundation\Concerns\ReplacesEnvironmentBootstrapper;
use RedFreak\ModularEnv\Foundation\Http\Kernel as RedFreakKernel;
use RedFreak\ModularEnv\Tests\TestCase;
use ReflectionProperty;
use stdClass;

class ReplacesEnvironmentBootstrapperTest extends TestCase
{
    use ReplacesEnvironmentBootstrapper;

    public function test_01_replacement(): void
    {
        // null
        $this->assertEquals([], $this->replaceEnvironmentBootstrapper(null));
        // empty-Array
        $this->assertEquals([], $this->replaceEnvironmentBootstrapper([]));
        // real replacements
        $sourceArray = [IlluminateLoadEnvironmentVariables::class];
        $expectedArray = [RedFreakLoadEnvironmentVariables::class];
        $this->assertEquals($expectedArray, $this->replaceEnvironmentBootstrapper($sourceArray));
        $sourceArray[] = IlluminateLoadEnvironmentVariables::class;
        $expectedArray[] = IlluminateLoadEnvironmentVariables::class;
        $this->assertEquals($expectedArray, $this->replaceEnvironmentBootstrapper($sourceArray));
        $sourceArray = [IlluminateLoadEnvironmentVariables::class, stdClass::class];
        $expectedArray = [RedFreakLoadEnvironmentVariables::class, stdClass::class];
        $this->assertEquals($expectedArray, $this->replaceEnvironmentBootstrapper($sourceArray));
        $sourceArray = [stdClass::class, IlluminateLoadEnvironmentVariables::class];
        $expectedArray = [stdClass::class, RedFreakLoadEnvironmentVariables::class];
        $this->assertEquals($expectedArray, $this->replaceEnvironmentBootstrapper($sourceArray));
        $sourceArray = [IlluminateLoadEnvironmentVariables::class, RedFreakLoadEnvironmentVariables::class];
        $expectedArray = [RedFreakLoadEnvironmentVariables::class, RedFreakLoadEnvironmentVariables::class];
        $this->assertEquals($expectedArray, $this->replaceEnvironmentBootstrapper($sourceArray));
    }
}
