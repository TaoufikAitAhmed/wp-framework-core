<?php

namespace themes\Wordpress\Framework\Core\Database\Factories\Concerns;

use themes\Wordpress\Framework\Core\Database\Factories\Factory;
use BadMethodCallException;

trait HasFactory
{
    /**
     * Get a new factory instance for the class.
     *
     * @param callable|array|int|null $count
     * @param callable|array          $state
     *
     * @return Factory<static>
     */
    public static function factory($count = null, $state = []): Factory
    {
        $factory = static::newFactory();

        return $factory
            ->count(is_numeric($count) ? $count : null)
            ->state(is_callable($count) || is_array($count) ? $count : $state);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<static>
     */
    protected static function newFactory(): Factory
    {
        throw new BadMethodCallException(sprintf('The method [newFactory] is not implemented in the %s class.', static::class));
    }
}
