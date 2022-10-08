<?php

namespace RedFreak\ModularEnv\Foundation\Concerns;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables as IlluminateLoadEnvironmentVariables;
use RedFreak\ModularEnv\Foundation\Bootstrap\LoadEnvironmentVariables as RedFreakLoadEnvironmentVariables;

trait ReplacesEnvironmentBootstrapper
{
    /**
     * @inheritDoc
     * We replace the {@link Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables LoadEnvironmentVariables}-Class
     * in the default Laravel Bootstrappers to bootstrap our own dotenv-Bootstrapper.
     */
    protected function bootstrappers(): array
    {
        return $this->replaceEnvironmentBootstrapper(parent::bootstrappers());
    }

    /**
     * Replace the {@link Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables LoadEnvironmentVariables}-bootstrapper.
     *   We do not override $this->bootstrappers, they could be changed by the child-Kernel.
     * @param  class-string[]  $bootstrappers
     *
     * @return class-string[]
     */
    private function replaceEnvironmentBootstrapper(?array $bootstrappers): array
    {
        // Just to make sure. Maybe there is a context where no bootstrapping is wanted, then we do not add anything
        $bootstrappers ??= [];

        // replace the original LoadEnvironmentVariables-Bootstrapper
        foreach($bootstrappers as &$bootstrapper) {
            if ($bootstrapper === IlluminateLoadEnvironmentVariables::class) {
                $bootstrapper = RedFreakLoadEnvironmentVariables::class;
                break;
            }
        }

        return $bootstrappers;
    }
}
