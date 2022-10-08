<?php

namespace RedFreak\ModularEnv\Tests;

use Orchestra\Testbench\TestCase as PackageTestCase;
use RedFreak\ModularEnv\ModularEnvServiceProvider;

class TestCase extends PackageTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            ModularEnvServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        //
    }
}
