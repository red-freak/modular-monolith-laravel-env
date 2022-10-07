<?php

namespace RedFreak\ModularEnv\Tests\Unit\Foundation\Http;

use Illuminate\Foundation\Http\Kernel as IlluminateKernel;
use RedFreak\ModularEnv\Foundation\Http\Kernel as RedFreakKernel;
use RedFreak\ModularEnv\Tests\TestCase;

class KernelTest extends TestCase
{
    public function test_inheritance() {
        $kernel = $this->app->make(RedFreakKernel::class);
        
        $this->assertInstanceOf(IlluminateKernel::class, $kernel);
    }
}
