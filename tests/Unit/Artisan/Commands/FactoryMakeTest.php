<?php

namespace themes\Wordpress\Framework\Core\Test\Commands;

use themes\Wordpress\Framework\Core\Artisan\Commands\FactoryMakeCommand;
use themes\Wordpress\Framework\Core\Database\Factories\AcfPartialFactory;
use themes\Wordpress\Framework\Core\Database\Factories\Factory;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

class FactoryMakeTest extends CommandMakerTestCase
{
    public function test_it_can_create_factory()
    {
        $output = $this->executeCommand('ListItemsFactory');

        // Assert the file was created
        $relativePath = 'database/factories/ListItemsFactory.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\Database\Factories\ListItemsFactory::class);
        $this->assertTrue($class->isSubclassOf(Factory::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace Database\Factories;
                
                use themes\Wordpress\Framework\Core\Database\Factories\Factory;
                
                class ListItemsFactory extends Factory
                {
                    /**
                     * Raw attributes of list items.
                     *
                     * @var array|null
                     */
                    protected ?array $properties = null;
                
                    /**
                     * Define the list items default state.
                     *
                     * @return array<string, mixed>
                     */
                    public function definition(): array
                    {
                        return [
                            //
                        ];
                    }
                
                    /**
                     * Generates entry of list items.
                     */
                    public function generate()
                    {
                        $this->properties = $this->getRawAttributes();
                        // TODO : Generate one list items.
                    }
                }
                
                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Factory was successfully composed.
        $this->assertStringContainsString('ListItemsFactory Factory successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('database/factories/ListItemsFactory.php', $output);
    }

    public function test_it_not_create_factory_if_factory_already_exists()
    {
        // Create ListItemsFactory file
        $viewModelPath = 'database/factories/ListItemsFactory.php';
        mkdir($this->getMockPath('database/factories'), 0777, true);
        file_put_contents($this->getMockPath($viewModelPath), ['Test Factory File Not Being Overwritten']);

        // Try to create a second ListItemsFactory
        $output = $this->executeCommand('ListItemsFactory');

        // Assert the default content factory is always there
        $this->assertStringContainsString('Test Factory File Not Being Overwritten', $this->getMockFileContents($viewModelPath));
        // Assert we see an error message
        $this->assertStringContainsString('Factory ListItemsFactory already exists.', $output);
    }

    public function test_it_can_create_factory_in_sub_folder()
    {
        $output = $this->executeCommand('ParentDirectory/ListItemsFactory');

        // Assert the file was created
        $relativePath = 'database/factories/ParentDirectory/ListItemsFactory.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\Database\Factories\ParentDirectory\ListItemsFactory::class);
        $this->assertTrue($class->isSubclassOf(Factory::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace Database\Factories\ParentDirectory;
                
                use themes\Wordpress\Framework\Core\Database\Factories\Factory;
                
                class ListItemsFactory extends Factory
                {
                    /**
                     * Raw attributes of list items.
                     *
                     * @var array|null
                     */
                    protected ?array $properties = null;
                
                    /**
                     * Define the list items default state.
                     *
                     * @return array<string, mixed>
                     */
                    public function definition(): array
                    {
                        return [
                            //
                        ];
                    }
                
                    /**
                     * Generates entry of list items.
                     */
                    public function generate()
                    {
                        $this->properties = $this->getRawAttributes();
                        // TODO : Generate one list items.
                    }
                }
                
                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Factory was successfully composed.
        $this->assertStringContainsString('ListItemsFactory Factory successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('database/factories/ParentDirectory/ListItemsFactory.php', $output);
    }

    public function test_it_does_not_create_factory_if_one_already_exists_in_sub_folder()
    {
        // Create ParentDirectory/ListItemsFactory file
        $viewModelPath = 'database/factories/ParentDirectory/ListItemsFactory.php';
        mkdir($this->getMockPath('database/factories/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($viewModelPath), ['Test Factory File Not Being Overwritten']);

        // Try to create a second ParentDirectory/ListItemsFactory
        $output = $this->executeCommand('ParentDirectory/ListItemsFactory');

        // Assert the default content factory is always there
        $this->assertStringContainsString('Test Factory File Not Being Overwritten', $this->getMockFileContents($viewModelPath));
        // Assert we see an error message
        $this->assertStringContainsString('Factory ListItemsFactory already exists.', $output);
    }

    public function test_it_can_create_an_acf_factory()
    {
        $output = $this->executeCommand('FooBarFactory', true);

        // Assert the file was created
        $relativePath = 'database/factories/FooBarFactory.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\Database\Factories\FooBarFactory::class);
        $this->assertTrue($class->isSubclassOf(AcfPartialFactory::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace Database\Factories;
                
                use themes\Wordpress\Framework\Core\Database\Factories\AcfPartialFactory;

                class FooBarFactory extends AcfPartialFactory
                {
                    /**
                     * The ACF Partial linked to this Factory.
                     *
                     * @var ?\themes\Wordpress\Framework\Core\Acf\Partial
                     */
                    protected $acfPartial = \App\Acf\Partials\FooBar::class;

                    /**
                     * Define the foo bar default state.
                     *
                     * @return array<string, mixed>
                     */
                    public function definition(): array
                    {
                        return [
                            //
                        ];
                    }
                }
                
                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Factory was successfully composed.
        $this->assertStringContainsString('FooBarFactory Factory successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('database/factories/FooBarFactory.php', $output);
    }

    protected function executeCommand($name, bool $acf = false): string
    {
        $app = $this->appWithMockBasePath();

        $command = new FactoryMakeCommand($app);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'name'  => $name,
            '--acf' => $acf,
        ]);

        return $commandTester->getDisplay();
    }
}
