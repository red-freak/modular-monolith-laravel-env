<?php

namespace RedFreak\ModularEnv\Foundation\Console;

use Illuminate\Foundation\Console\Kernel as IlluminateConsoleKernel;
use RedFreak\ModularEnv\Foundation\Concerns\ReplacesEnvironmentBootstrapper;

class Kernel extends IlluminateConsoleKernel
{
    use ReplacesEnvironmentBootstrapper;
}
