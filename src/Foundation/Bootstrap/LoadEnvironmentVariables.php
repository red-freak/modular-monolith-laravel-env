<?php

namespace RedFreak\ModularEnv\Foundation\Bootstrap;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables as IlluminateLoadEnvironmentVariables;
use Illuminate\Support\Collection;
use Illuminate\Support\Env;
use Illuminate\Support\Str;
use RedFreak\ModularEnv\Contracts\ModularEnvironmentApplication as ModularEnvironmentApplicationContract;
use RedFreak\ModularEnv\Dotenv\Dotenv;

class LoadEnvironmentVariables extends IlluminateLoadEnvironmentVariables
{
    protected ?Filesystem $filesystem = null;

    /**
     * @inheritDoc
     * We add the pathes of the addional
     * to bootstrap own Bootstraper for the dotenv-Files.
     */
    protected function createDotenv($app)
    {
        return Dotenv::create(
            Env::getRepository(),
            $this->environmentPaths($app),
            $app->environmentFile(),
            false,
            null,
            $app->environmentPath()
        );
    }

    protected function filesystem(): Filesystem
    {
        if (!$this->filesystem) {
            $this->filesystem = new Filesystem();
        }

        return $this->filesystem;
    }

    /**
     * Get the paths to the environment file directories.
     *
     * @param  ApplicationContract  $app
     *
     * @return string[]
     */
    public function environmentPaths(ApplicationContract $app): array
    {
        $paths              = [$app->environmentPath()];
        $pathsWithWildcards = [];

        foreach($this->additionalEnvironmentPathsRegex($app) as $path) {
            // create a basePath from relative path if needed
            $path = $this->basePath($path, $app);
            // IF there is no "regex" THEN add the path
            if (!Str::contains($path, '*') && $this->filesystem()->isDirectory($path)) { // also matches **
                $paths[] = $path;
                continue;
            }
            // ELSE we use Finder to do the job (later)
            $pathsWithWildcards[] = $path;
        }

        return array_unique(array_filter(array_merge($paths, $this->findPaths($pathsWithWildcards))));
    }

    /**
     * @param  string[]  $paths
     *
     * @return string[]
     */
    private function findPaths(array $paths): array
    {
        $pathsFound = [];
        foreach($paths as $path) {
            $pathsFound[] = $this->findPath(collect(explode(DIRECTORY_SEPARATOR, $path)));
        }

        // we are flattening our array
        return array_merge([], ...$pathsFound);
    }

    /**
     * Analyse the given patterns and return matching directories.
     *
     * @param  Collection  $pathSegmentsToAnalyse
     * @param  string      $currentPath
     * @param  int         $depth                 the depth of the recursions for debugging
     *
     * @return array
     */
    private function findPath(Collection $pathSegmentsToAnalyse, string $currentPath = '', int $depth = 0): array
    {
        // handle all segements withour wildcards
        [$pathSegmentToAnalyse, $currentPathSegments] = $this->processPathSegmentsWithoutWildcard($pathSegmentsToAnalyse);

        // construct the currentPath until here
        $currentPath .= implode(DIRECTORY_SEPARATOR, array_filter($currentPathSegments));
        if (!$this->filesystem()->isDirectory($currentPath)) return [];

        if (empty($pathSegmentToAnalyse)) return [$currentPath.DIRECTORY_SEPARATOR];

        if ($pathSegmentToAnalyse === '**') $pathSegmentToAnalyse = '*';
        // get the directories of the currentPath
        $pathsFound = $this->filesystem()->glob($currentPath.DIRECTORY_SEPARATOR.$pathSegmentToAnalyse);
        // if there are no pathes, then we do not have to do more analysing
        if (!count($pathsFound)) {
            return [];
        }

        // analyse the rest of the segments (recursion)
        $pathsToReturn = [];
        foreach($pathsFound as $path) {
            // work with a copy of the collection
            $pathsToReturn[] = $this->findPath($pathSegmentsToAnalyse->collect(), $path, ++$depth);
        }

        // flatten (prevented to use array_merge in loop before), filter, unique
        return array_unique(array_filter(array_merge([], ...$pathsToReturn)));
    }

    /**
     * Get the additional paths to environment file directories.
     *
     * @param  ApplicationContract  $app
     *
     * @return string[]
     */
    private function additionalEnvironmentPathsRegex(ApplicationContract $app): array
    {
        if (!$app instanceof ModularEnvironmentApplicationContract) {
            return [DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'**'.DIRECTORY_SEPARATOR];
        }

        return $app->additionalEnvFiles();
    }

    /**
     * @param  string  $pathRegex
     * @param  ApplicationContract  $app
     *
     * @return string
     */
    private function basePath(string $pathRegex, ApplicationContract $app): string
    {
        if ( ! Str::startsWith($pathRegex, $app->basePath())) {
            // replace double separators if present
            $pathRegex = Str::replace(
                DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR,
                $app->basePath($pathRegex)
            );
        }

        return $pathRegex;
    }

    /**
     * @param  Collection  $pathSegmentsToAnalyse
     *
     * @return array [the wildcard-segment, array of analysed segments without wildcards]
     */
    private function processPathSegmentsWithoutWildcard(Collection $pathSegmentsToAnalyse): array
    {
        $pathSegmentToAnalyse = null;
        $currentPathSegments  = [];
        do {
            $currentPathSegments[] = $pathSegmentToAnalyse;
            $pathSegmentToAnalyse  = $pathSegmentsToAnalyse->shift();
        } while (
            ! empty($pathSegmentToAnalyse) // the segment is not empty
            && ! $pathSegmentsToAnalyse->isEmpty() // the segment-collection is not empty
            && ! Str::contains($pathSegmentToAnalyse, '*') // there is no wildcard in the segment
        );

        return [$pathSegmentToAnalyse, $currentPathSegments];
    }
}
