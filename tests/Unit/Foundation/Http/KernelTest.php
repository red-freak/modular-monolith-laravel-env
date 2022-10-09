<?php

namespace RedFreak\ModularEnv\Tests\Unit\Foundation\Http;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables as IlluminateLoadEnvironmentVariables;
use Illuminate\Foundation\Http\Kernel as IlluminateKernel;
use Illuminate\Support\Facades\Facade;
use RedFreak\ModularEnv\Foundation\Bootstrap\LoadEnvironmentVariables as RedFreakLoadEnvironmentVariables;
use RedFreak\ModularEnv\Foundation\Concerns\ReplacesEnvironmentBootstrapper;
use RedFreak\ModularEnv\Foundation\Http\Kernel as RedFreakKernel;
use RedFreak\ModularEnv\Tests\TestCase;
use ReflectionException;
use ReflectionProperty;

class KernelTest extends TestCase
{
    use ReplacesEnvironmentBootstrapper;

    public function test_01_inheritance(): void
    {
        $kernel = $this->app->make(RedFreakKernel::class);
        
        $this->assertInstanceOf(IlluminateKernel::class, $kernel);
    }

    public function test_02_bootstrapper_replacement(): void
    {
        $illuminateBootstrappersProperty = new ReflectionProperty(IlluminateKernel::class, 'bootstrappers');
        $defaultBootstrappers = $illuminateBootstrappersProperty->getDefaultValue();

        $replacedBootstrappers = $this->replaceEnvironmentBootstrapper($defaultBootstrappers);

        // default array
        $this->assertContains(IlluminateLoadEnvironmentVariables::class, $defaultBootstrappers, 'The Laravel default bootstrapper is not present on Kernel default.');
        $this->assertNotContains(RedFreakLoadEnvironmentVariables::class, $defaultBootstrappers, 'The RedFreak bootstrapper is present on Kernel default.');

        // replaced array
        $this->assertNotContains(IlluminateLoadEnvironmentVariables::class, $replacedBootstrappers, 'The Laravel default bootstrapper is present after replacement.');
        $this->assertContains(RedFreakLoadEnvironmentVariables::class, $replacedBootstrappers, 'The RedFreak bootstrapper is not present after replacement.');

        $this->assertCount(
            count($defaultBootstrappers), $replacedBootstrappers,
            'The number of bootstrappers changed during replacement.'
        );

        // we know that the replacement worked in general. so we delete the bootstrapper-position off of both arrays and compare them to ensure no side effects kicked in
        $indexOfBootstrapper = array_search(RedFreakLoadEnvironmentVariables::class, $replacedBootstrappers);
        unset($defaultBootstrappers[$indexOfBootstrapper], $replacedBootstrappers[$indexOfBootstrapper]);
        $this->assertEquals($defaultBootstrappers, $replacedBootstrappers, 'During the replacement one ore more side effects occured.');
    }

    /**
     * @throws ReflectionException
     */
    public function test_02_no_usage_of_facades(): void
    {
        $this->checkForFacadeInheritance(IlluminateKernel::class, [Facade::class]);
    }
}
