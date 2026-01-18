<?php

namespace themes\Wordpress\Framework\Core\Test\Commands;

use themes\Wordpress\Framework\Core\Artisan\Commands\SeederMakeCommand;
use themes\Wordpress\Framework\Core\Database\Seeder;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SeederMakeTest extends CommandMakerTestCase
{
    public function test_it_can_create_seeder()
    {
        $output = $this->executeCommand('MySeeder');

        // Assert the file was created
        $relativePath = 'database/seeders/MySeeder.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\Database\Seeders\MySeeder::class);
        $this->assertTrue($class->isSubclassOf(Seeder::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php
                
                namespace Database\Seeders;
                
                use themes\Wordpress\Framework\Core\Database\Seeder;
                
                class MySeeder extends Seeder
                {
                    /**
                     * Run the database seeds.
                     *
                     * @return void
                     */
                    public function run(): void
                    {
                        //
                    }
                }
                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Seeder was successfully composed.
        $this->assertStringContainsString('MySeeder Seeder successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('database/seeders/MySeeder.php', $output);
    }

    public function test_it_does_not_create_seeder_if_one_already_exists()
    {
        // Create MySeeder file
        $postTypePath = 'database/seeders/MySeeder.php';
        mkdir($this->getMockPath('database/seeders'), 0777, true);
        file_put_contents($this->getMockPath($postTypePath), ['Test Seeder File Not Being Overwritten']);

        // Try to create a second MySeeder
        $output = $this->executeCommand('MySeeder');

        // Assert the default content seeder is always there
        $this->assertStringContainsString('Test Seeder File Not Being Overwritten', $this->getMockFileContents($postTypePath));
        // Assert we see an error message
        $this->assertStringContainsString('Seeder MySeeder already exists.', $output);
    }

    public function test_it_is_possible_to_create_seeder_in_sub_folder()
    {
        $output = $this->executeCommand('ParentDirectory/MySeeder');

        // Assert the file was created
        $relativePath = 'database/seeders/ParentDirectory/MySeeder.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\Database\Seeders\ParentDirectory\MySeeder::class);
        $this->assertTrue($class->isSubclassOf(Seeder::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php
                
                namespace Database\Seeders\ParentDirectory;
                
                use themes\Wordpress\Framework\Core\Database\Seeder;
                
                class MySeeder extends Seeder
                {
                    /**
                     * Run the database seeds.
                     *
                     * @return void
                     */
                    public function run(): void
                    {
                        //
                    }
                }
                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Seeder was successfully composed.
        $this->assertStringContainsString('MySeeder Seeder successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('database/seeders/ParentDirectory/MySeeder.php', $output);
    }

    public function test_it_does_not_create_seeder_if_one_already_exists_in_sub_folder()
    {
        // Create Product file
        $postTypePath = 'database/seeders/ParentDirectory/MySeeder.php';
        mkdir($this->getMockPath('database/seeders/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($postTypePath), ['Test Seeder File Not Being Overwritten']);

        // Try to create a second MySeeder
        $output = $this->executeCommand('ParentDirectory/MySeeder');

        // Assert the default content post type is always there
        $this->assertStringContainsString('Test Seeder File Not Being Overwritten', $this->getMockFileContents($postTypePath));
        // Assert we see an error message
        $this->assertStringContainsString('Seeder MySeeder already exists.', $output);
    }

    protected function executeCommand($name): string
    {
        $app = $this->appWithMockBasePath();

        $command = new SeederMakeCommand($app);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['name' => $name]);

        return $commandTester->getDisplay();
    }
}
