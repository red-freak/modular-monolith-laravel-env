<?php

namespace RedFreak\ModularEnv\Contracts;

/**
 * Contract for declaring additional dotenv-files to be loaded by the package red-freak/modular-monolith-laravel-env
 */
interface ModularEnvironmentApplication
{
    /**
     * beside the environment-driven dotenv-files in the project root directory, these files should be interpreted.
     *
     * @return array<string> Paths defining other dotenv files (* and ** are allowed and used like in the .gitignore).
     */
    public function additionalEnvFiles(): array;
}
