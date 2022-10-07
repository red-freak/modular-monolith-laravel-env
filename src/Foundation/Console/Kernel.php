<?php

namespace RedFreak\ModularEnv\Foundation\Console;

use Illuminate\Foundation\Console\Kernel as IlluminateConsoleKernel;

class Kernel extends IlluminateConsoleKernel
{
    /**
     * @inheritDoc
     * We replace the {@link Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables LoadEnvironmentVariables}-Class
     * in the default Laravel Bootstrappers to bootstrap our own dotenv-Bootstrapper.
     */
    protected function bootstrappers(): array
    {
        return parent::bootstrappers();
    }
}
