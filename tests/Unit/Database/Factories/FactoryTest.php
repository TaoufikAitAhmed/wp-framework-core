<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Database\Factories;

use themes\Wordpress\Framework\Core\Database\Factories\Concerns\HasFactory;
use themes\Wordpress\Framework\Core\Database\Factories\Factory;
use themes\Wordpress\Framework\Core\Test\WordpressTestCase;
use Illuminate\Support\Collection;

/**
 * @preserveGlobalState disabled
 */
class FactoryTest extends WordpressTestCase
{
    /**
     * @preserveGlobalState disabled
     * @runInSeparateProcess
     */
    public function test_basic_factory_can_be_created()
    {
        $user = FactoryTestUserFactory::new()->make();
        $this->assertInstanceOf(User::class, $user);

        $user = FactoryTestUserFactory::new()->make(['name' => 'Théo Benoit']);
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('Théo Benoit', $user->name);

        $users = FactoryTestUserFactory::new()->makeMany([
            ['name' => 'Théo Benoit'],
            ['name' => 'John Doe'],
        ]);
        $this->assertInstanceOf(Collection::class, $users);
        $this->assertCount(2, $users);

        $users = FactoryTestUserFactory::times(10)->make();
        $this->assertCount(10, $users);
    }

    /**
     * @preserveGlobalState disabled
     * @runInSeparateProcess
     */
    public function test_it_throw_an_exception_if_the_new_factory_method_is_not_defined()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage(
            'The method [newFactory] is not implemented in the themes\Wordpress\Framework\Core\Test\Unit\Database\Factories\ClassWithFactoryButWithoutNewFactoryMethod class.'
        );
        $factoryClass = new ClassWithFactoryButWithoutNewFactoryMethod();
        $factoryClass::factory();
    }

    /**
     * @preserveGlobalState disabled
     * @runInSeparateProcess
     */
    public function test_aclass_can_have_afactory()
    {
        $factoryClass = new ClassWithFactoryMethod();
        $this->assertInstanceOf(FactoryTestUserFactory::class, $factoryClass::factory());
    }
}

class FactoryTestUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'    => $this->faker->name,
            'options' => null,
        ];
    }

    public function generate()
    {
        return new User($this->getRawAttributes()['name'], $this->getRawAttributes()['options']);
    }

    public function delete(): void
    {
        // TODO: Implement delete() method.
    }
}

class ClassWithFactoryButWithoutNewFactoryMethod
{
    use HasFactory;
}

class ClassWithFactoryMethod
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<static>
     */
    protected static function newFactory(): Factory
    {
        return new FactoryTestUserFactory();
    }
}

class User
{
    public ?string $name;

    public ?string $options;

    public function __construct(?string $name = null, ?string $options = null)
    {
        $this->name = $name;
        $this->options = $options;
    }
}
