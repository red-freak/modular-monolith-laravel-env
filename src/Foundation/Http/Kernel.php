<?php

namespace RedFreak\ModularEnv\Foundation\Http;

use Illuminate\Foundation\Http\Kernel as IlluminateHttpKernel;
use RedFreak\ModularEnv\Foundation\Concerns\ReplacesEnvironmentBootstrapper;

class Kernel extends IlluminateHttpKernel
{
    use ReplacesEnvironmentBootstrapper;
}
