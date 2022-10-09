<?php

namespace RedFreak\ModularEnv\Dotenv\Store;

use Dotenv\Store\File\Paths;
use Dotenv\Store\StoreBuilder as DotenvStoreBuilder;
use Dotenv\Store\StoreInterface;

class StoreBuilder // extends Dotenv\Store\StoreBuilder indirectly
{
    /**
     * The of default name.
     */
    private const DEFAULT_NAME = '.env';

    /**
     * Create a new store builder instance.
     *
     * @param  DotenvStoreBuilder  $storeBuilder
     * @param  string[]  $paths
     * @param  string[]  $names
     * @param  bool  $shortCircuit
     * @param  string|null  $fileEncoding
     */
    private function __construct(
        private readonly DotenvStoreBuilder $storeBuilder,
        private readonly array $paths = [],
        private readonly array $names = [],
        private readonly bool $shortCircuit = false,
        private readonly ?string $fileEncoding = null
    ) {}

    /**
     * Create a new store builder instance with no names.
     *
     * @return self
     */
    public static function createWithNoNames()
    {
        return new self(DotenvStoreBuilder::createWithNoNames());
    }

    /**
     * Create a new store builder instance with the default name.
     *
     * @return self
     */
    public static function createWithDefaultName()
    {
        return new self(DotenvStoreBuilder::createWithDefaultName(), [], [self::DEFAULT_NAME]);
    }

    /**
     * Creates a store builder with the given path added.
     *
     * @param string $path
     *
     * @return self
     */
    public function addPath(string $path)
    {
        return new self($this->storeBuilder->addPath($path), \array_merge($this->paths, [$path]), $this->names, $this->shortCircuit, $this->fileEncoding);
    }

    /**
     * Creates a store builder with the given name added.
     *
     * @param string $name
     *
     * @return self
     */
    public function addName(string $name)
    {
        return new self($this->storeBuilder->addName($name), $this->paths, \array_merge($this->names, [$name]), $this->shortCircuit, $this->fileEncoding);
    }

    /**
     * Creates a store builder with short circuit mode enabled.
     *
     * @return self
     */
    public function shortCircuit()
    {
        return new self($this->storeBuilder->shortCircuit(), $this->paths, $this->names, true, $this->fileEncoding);
    }

    /**
     * Creates a store builder with the specified file encoding.
     *
     * @param string|null $fileEncoding
     *
     * @return self
     */
    public function fileEncoding(string $fileEncoding = null)
    {
        return new self($this->storeBuilder->fileEncoding($fileEncoding), $this->paths, $this->names, $this->shortCircuit, $fileEncoding);
    }

    /**
     * Creates a new store instance.
     *
     * @return StoreInterface
     */
    public function make(): StoreInterface
    {
        return new FileStore(
            Paths::filePaths($this->paths, $this->names),
            $this->shortCircuit,
            $this->fileEncoding
        );
    }
}
