<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

abstract class MakeFromStubCommand extends Command
{
    protected function createFile($relativePath, $contents)
    {
        $absolutePath = "{$this->app->basePath()}/{$relativePath}";
        $directory = dirname($absolutePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0754, true);
        }

        file_put_contents($absolutePath, $contents);
    }

    /**
     * Return the block creation summary.
     *
     * @param              $fileName
     * @param string|array $filePath
     * @param              $type
     *
     * @return array
     */
    protected function summary($fileName, $filePath, $type): array
    {
        if (is_array($filePath)) {
            $summary = ["🎉 <fg=blue;options=bold>{$fileName}</> {$type} successfully composed."];
            foreach ($filePath as $path) {
                $summary[] = "     ⮑  <fg=blue>{$path}</>";
            }

            return $summary;
        }

        return ["🎉 <fg=blue;options=bold>{$fileName}</> {$type} successfully composed.", "     ⮑  <fg=blue>{$filePath}</>"];
    }
}
