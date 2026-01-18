<?php

namespace themes\Wordpress\Framework\Core\Database\Factories;

use Closure;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Collection;

abstract class Factory
{
    /**
     * The current Faker instance.
     *
     * @var Generator
     */
    protected Generator $faker;

    /**
     * The number of factories that should be generated.
     *
     * @var int|null
     */
    protected ?int $count;

    /**
     * The state transformations that will be applied to the factory.
     *
     * @var Collection
     */
    protected Collection $states;

    /**
     * The "after creating" callbacks that will be applied to the factory.
     *
     * @var Collection
     */
    protected Collection $afterCreating;

    /**
     * Create a new factory instance.
     *
     * @param int|null        $count
     * @param Collection|null $states
     * @param Collection|null $afterCreating
     */
    public function __construct(?int $count = null, ?Collection $states = null, ?Collection $afterCreating = null)
    {
        $this->count = $count;
        $this->states = $states ?? new Collection();
        $this->afterCreating = $afterCreating ?? new Collection();
        $this->faker = FakerFactory::create('fr_FR');
    }

    /**
     * Get a new factory instance for the given number of models.
     *
     * @param int $count
     *
     * @return static
     */
    public static function times(int $count): self
    {
        return static::new()->count($count);
    }

    /**
     * Create a collection of factories and persist them to the database.
     *
     * @param iterable<int, array<string, mixed>> $records
     *
     * @return Collection
     */
    public function makeMany(iterable $records): Collection
    {
        return new Collection(
            collect($records)->map(function ($record) {
                return $this->state($record)->make();
            })
        );
    }

    /**
     * Get a new factory instance for the given attributes.
     *
     * @param (callable(): array<string, mixed>)|array<string, mixed> $attributes
     *
     * @return static
     */
    public static function new($attributes = []): self
    {
        return (new static())->state($attributes)->configure();
    }

    /**
     * Configure the factory.
     *
     * @return $this
     */
    public function configure(): self
    {
        return $this;
    }

    /**
     * Define the factory's default state.
     *
     * @return array<string, mixed>
     */
    abstract public function definition(): array;

    /**
     * Generates entry of factory.
     *
     * @return mixed
     */
    abstract public function generate();

    /**
     * Specify how many factories should be generated.
     *
     * @param int|null $count
     *
     * @return static
     */
    public function count(?int $count): self
    {
        return $this->newInstance(['count' => $count]);
    }

    /**
     * Create a collection of models.
     *
     * @param (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed> $attributes
     *
     * @return Collection|mixed
     */
    public function make($attributes = [])
    {
        if (!empty($attributes)) {
            $results = $this->state($attributes)->make([]);
            $this->callAfterCreating([$results]);

            return $results;
        }

        if ($this->count === null) {
            $results = $this->generate();
            $this->callAfterCreating([$results]);

            return $results;
        }

        if ($this->count < 1) {
            $results = $this->newInstance()->generate();
            $this->callAfterCreating([$results]);

            return $results;
        }

        $results = new Collection(
            array_map(
                fn () => $this->newInstance()->generate(),
                range(1, $this->count)
            )
        );
        $this->callafterCreating($results->toArray());

        return $results;
    }

    /**
     * Add a new "after creating" callback to the model definition.
     *
     * @param Closure(): mixed $callback
     *
     * @return static
     */
    public function afterCreating(Closure $callback)
    {
        return $this->newInstance(['afterCreating' => $this->afterCreating->concat([$callback])]);
    }

    /**
     * Add a new state transformation to the factory definition.
     *
     * @param callable|array $state
     *
     * @return static
     */
    public function state($state): self
    {
        return $this->newInstance([
            'states' => $this->states->concat([
                is_callable($state) ? $state : function () use ($state) {
                    return $state;
                },
            ]),
        ]);
    }

    /**
     * Die and dump the attributes generated.
     *
     * @return void
     */
    public function dd(): void
    {
        dd($this->getRawAttributes());
    }

    /**
     * Call the "after creating" callbacks for the given model instances.
     *
     * @param array $instances
     *
     * @return void
     */
    protected function callAfterCreating(array $instances)
    {
        foreach ($instances as $instance) {
            foreach ($this->afterCreating as $callback) {
                $callback($instance);
            }
        }
    }

    /**
     * Get the raw attributes for the factory as an array.
     *
     * @return array
     */
    protected function getRawAttributes(): array
    {
        return $this
            ->states
            ->reduce(fn ($carry, $state) => array_merge($carry, $state($carry)), $this->definition());
    }

    /**
     * Create a new instance of the factory builder with the given mutated properties.
     *
     * @param array $arguments
     *
     * @return static
     */
    protected function newInstance(array $arguments = []): self
    {
        return new static(
            ...array_values(
                array_merge([
                    'count'         => $this->count,
                    'states'        => $this->states,
                    'afterCreating' => $this->afterCreating,
                ], $arguments)
            )
        );
    }
}
