<?php

namespace RedFreak\ModularEnv\Contracts;

interface ModularEnvironmentApplication
{
    /**
     * beside the environment-driven dotenv-files in the project root directory, these files should be interpreted.
     *
     * @return array<string> Paths defining other dotenv files (* and ** are allowed like used in .gitignore).
     */
    public function additionalEnvFiles(): array;
}
