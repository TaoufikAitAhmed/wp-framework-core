<?php

namespace themes\Wordpress\Framework\Core\Acf\Concerns;

use themes\Wordpress\Framework\Core\Acf\Partial;
use Blast\Facades\AbstractFacade;
use InvalidArgumentException;
use ReflectionClass;
use StoutLogic\AcfBuilder\FieldsBuilder;

trait InteractsWithPartial
{
    /**
     * Compose a field partial instance or file.
     *
     * @param string|array|null $partial
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws InvalidArgumentException
     */
    protected function get($partial = null)
    {
        if (is_subclass_of($partial, AbstractFacade::class)) {
            $partial = get_class($partial::__instance());
        }

        if (is_subclass_of($partial, Partial::class) && !(new ReflectionClass($partial))->isAbstract()) {
            return $this->app->make($partial)->compose();
        }

        if (is_a($partial, FieldsBuilder::class)) {
            return $partial;
        }

        throw new InvalidArgumentException("Could not find $partial. It can come from the autoloader. Please regenerate the autoloader.");
    }
}
