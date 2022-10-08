<?php

namespace RedFreak\ModularEnv\Foundation\Bootstrap;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables as IlluminateLoadEnvironmentVariables;

class LoadEnvironmentVariables extends IlluminateLoadEnvironmentVariables
{
    /**
     * @inheritDoc
     * We add the pathes of the addional
     * to bootstrap own Bootstraper for the dotenv-Files.
     */
    protected function createDotenv($app)
    {
        return parent::createDotenv($app);
    }
}
