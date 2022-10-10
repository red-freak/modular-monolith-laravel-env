<?php

namespace RedFreak\ModularEnv;

use Illuminate\Support\ServiceProvider;

class ModularEnvServiceProvider extends ServiceProvider
{
    public const PACKAGE_VERSION = '0.2.1';

    public function register()
    {
        //
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) $this->registerCommands();
    }

    protected function registerCommands(): void
    {

    }
}
