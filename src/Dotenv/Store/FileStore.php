<?php

namespace RedFreak\ModularEnv\Dotenv\Store;

use Dotenv\Exception\InvalidPathException;
use Dotenv\Store\File\Reader;
use Dotenv\Store\StoreInterface;
use Illuminate\Support\Str;
use RedFreak\ModularEnv\Dotenv\Dotenv;

class FileStore implements StoreInterface
{
    /**
     * Create a new file store instance.
     *
     * @param string[]    $filePaths
     * @param bool        $shortCircuit
     * @param string|null $fileEncoding
     *
     * @return void
     */
    public function __construct(
        private readonly array $filePaths,
        private readonly bool $shortCircuit,
        private readonly ?string $fileEncoding = null
    ) {}

    public function read()
    {
        if ($this->filePaths === []) {
            throw new InvalidPathException('At least one environment file path must be provided.');
        }

        $contents = Reader::read($this->filePaths, $this->shortCircuit, $this->fileEncoding);
        $contents = $this->prefixLines($contents);

        if (\count($contents) > 0) {
            return \implode("\n", $contents);
        }

        throw new InvalidPathException(
            \sprintf('Unable to read any of the environment file(s) at [%s].', \implode(', ', $this->filePaths))
        );
    }

    /**
     * prefix the variables
     *
     * @param  array  $contents
     *
     * @return array
     */
    private function prefixLines(array $contents): array
    {
        foreach ($contents as $filePath => &$contentBlock) {
            if (Str::startsWith($filePath, Dotenv::laravelDefaultPath().DIRECTORY_SEPARATOR.'.env')) {
                continue;
            }
            $modulePrefix = Str::upper(Str::snake(basename(
                Str::replaceLast(DIRECTORY_SEPARATOR.basename($filePath), '', $filePath))
            ));

            $rnPresent = str_contains($contentBlock, "\r\n");
            $nPresent  = str_contains($contentBlock, "\n");

            if ($rnPresent) {
                $separator = "\r\n";
            } elseif ($nPresent) {
                $separator = "\n";
            } else {
                $separator = "\r";
            }
            $contentElements = explode($separator, $contentBlock);
            foreach ($contentElements as &$line) {
                if ( ! empty($line)) {
                    $line = $modulePrefix.'__'.$line;
                }
            }
            unset($line);

            $contentBlock = implode($separator, $contentElements);
        }
        unset($contentBlock);

        return $contents;
    }
}
