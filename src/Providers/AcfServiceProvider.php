<?php

namespace themes\Wordpress\Framework\Core\Providers;

use themes\Wordpress\Framework\Core\Acf\Composer;
use themes\Wordpress\Framework\Core\Acf\Options;
use themes\Wordpress\Framework\Core\Acf\Partial;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class AcfServiceProvider extends ServiceProvider
{
    /**
     * Option page names.
     *
     * @var array
     */
    protected static $optionPageNames = [];

    /**
     * Acf options classes.
     *
     * @var Collection
     */
    public Collection $acfOptions;

    /**
     * Bootstrap services.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function boot()
    {
        $this->acfOptions = new Collection();
        $finder = new Finder();

        $finder->files()->in($this->app->basePath() . '/app/Acf');

        foreach ($finder as $file) {
            $className = $file->getFilenameWithoutExtension();
            $relativePath = str_replace('/', '\\', $file->getRelativePath());
            $class = "App\\Acf\\{$relativePath}\\$className";

            if (!class_exists($class)) {
                continue;
            }

            include_once $file->getRealPath();

            $class = $this->app->make($class);

            if (is_subclass_of($class, Options::class) && !(new ReflectionClass($class))->isAbstract()) {
                // If there is a parent for this option page
                // Instantiate it, and check if the array containing
                // all our options pages doesn't already contains it.
                // If not, push the parent page to the acf options page array
                if ($class->parent) {
                    $optionParent = $this->app->has($class->parent) ? $this->app->get($class->parent) : $this->app->make($class->parent);

                    if (!$this->acfOptions->contains($optionParent) && is_subclass_of($optionParent, Options::class)) {
                        $this->acfOptions->push($optionParent);
                    }
                }

                // We might already have found this option page thanks to parent of other option page,
                // so continue the foreach loop
                if ($this->acfOptions->contains($class)) {
                    continue;
                }

                $this->acfOptions->push($class);
            }

            if (!is_subclass_of($class, Composer::class) || is_subclass_of($class, Partial::class) || (new ReflectionClass($class))->isAbstract()) {
                continue;
            }

            $class->compose();
        }

        // Instantiate all our acf options pages if there are.
        if ($this->acfOptions) {
            $this->loadAcfOptions();
        }
    }

    /**
     * Load Acf options pages.
     */
    public function loadAcfOptions(): void
    {
        $this->acfOptions->map(function (Options $optionPage) {
            if (in_array($optionPage->name, self::$optionPageNames)) {
                throw new InvalidArgumentException(
                    "The name '{$optionPage->name}' is used several times for option pages. Please specify a unique name for each option page."
                );
            }
            self::$optionPageNames[] = $optionPage->name;
            $optionPage->compose();
        });
    }
}
