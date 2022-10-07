<?php

namespace RedFreak\ModularEnv\Tests\Unit\Foundation\Console;

use Illuminate\Foundation\Console\Kernel as IlluminateKernel;
use RedFreak\ModularEnv\Foundation\Console\Kernel as RedFreakKernel;
use RedFreak\ModularEnv\Tests\TestCase;

class KernelTest extends TestCase
{
    public function test_inheritance() {
        $kernel = $this->app->make(RedFreakKernel::class);
        
        $this->assertInstanceOf(IlluminateKernel::class, $kernel);
    }
}
