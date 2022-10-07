<?php

namespace RedFreak\ModularEnv\Tests\Unit\Foundation\Bootstrap;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables as IlluminateLoadEnvironmentVariables;
use RedFreak\ModularEnv\Foundation\Bootstrap\LoadEnvironmentVariables as RedFreakLoadEnvironmentVariables;
use RedFreak\ModularEnv\Tests\TestCase;

class LoadEnvironmentVariablesTest extends TestCase
{
    public function test_inheritance() {
        $bootstrapper = new RedFreakLoadEnvironmentVariables();

        $this->assertInstanceOf(IlluminateLoadEnvironmentVariables::class, $bootstrapper);
    }
}
