<?php

namespace RedFreak\ModularEnv\Foundation\Http;

use Illuminate\Foundation\Http\Kernel as IlluminateHttpKernel;

class Kernel extends IlluminateHttpKernel {
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
