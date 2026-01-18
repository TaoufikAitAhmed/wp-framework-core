<?php

namespace themes\Wordpress\Framework\Core\Test\Commands;

use themes\Wordpress\Framework\Core\Artisan\Commands\ServiceProviderMakeCommand;
use themes\Wordpress\Framework\Core\Providers\ServiceProvider;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

class ServiceProviderMakeTest extends CommandMakerTestCase
{
    public function test_it_can_create_service_provider()
    {
        $output = $this->executeCommand('MyServiceProvider');

        // Assert the file was created
        $relativePath = 'app/Providers/MyServiceProvider.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Providers\MyServiceProvider::class);
        $this->assertTrue($class->isSubclassOf(ServiceProvider::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php
                
                namespace App\Providers;
                
                use themes\Wordpress\Framework\Core\Providers\ServiceProvider;
                
                class MyServiceProvider extends ServiceProvider
                {
                    /**
                     * Register required items with the Application Container
                     *
                     * @return void
                     */
                    public function register()
                    {
                        //
                    }
                
                    /**
                     * Perform any required boot operations
                     *
                     * @return void
                     */
                    public function boot()
                    {
                        //
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Service Provider was successfully composed.
        $this->assertStringContainsString('MyServiceProvider Provider successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Providers/MyServiceProvider.php', $output);
    }

    public function test_it_not_create_service_provider_if_service_provider_already_exists()
    {
        // Create MyServiceProvider file
        $serviceProviderPath = 'app/Providers/MyServiceProvider.php';
        mkdir($this->getMockPath('app/Providers'), 0777, true);
        file_put_contents($this->getMockPath($serviceProviderPath), ['Test Service Provider File Not Being Overwritten']);

        // Try to create a second MyServiceProvider
        $output = $this->executeCommand('MyServiceProvider');

        // Assert the default content service provider is always there
        $this->assertStringContainsString('Test Service Provider File Not Being Overwritten', $this->getMockFileContents($serviceProviderPath));
        // Assert we see an error message
        $this->assertStringContainsString('Provider MyServiceProvider already exists.', $output);
    }

    public function test_it_can_create_service_provider_in_sub_folder()
    {
        $output = $this->executeCommand('ParentDirectory/MyServiceProvider');

        // Assert the file was created
        $relativePath = 'app/Providers/ParentDirectory/MyServiceProvider.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Providers\ParentDirectory\MyServiceProvider::class);
        $this->assertTrue($class->isSubclassOf(ServiceProvider::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php
                
                namespace App\Providers\ParentDirectory;
                
                use themes\Wordpress\Framework\Core\Providers\ServiceProvider;
                
                class MyServiceProvider extends ServiceProvider
                {
                    /**
                     * Register required items with the Application Container
                     *
                     * @return void
                     */
                    public function register()
                    {
                        //
                    }
                
                    /**
                     * Perform any required boot operations
                     *
                     * @return void
                     */
                    public function boot()
                    {
                        //
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Service Provider was successfully composed.
        $this->assertStringContainsString('MyServiceProvider Provider successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Providers/ParentDirectory/MyServiceProvider.php', $output);
    }

    public function test_it_does_not_create_service_provider_if_one_already_exists_in_sub_folder()
    {
        // Create ParentDirectory/MyServiceProvider file
        $serviceProviderPath = 'app/Providers/ParentDirectory/MyServiceProvider.php';
        mkdir($this->getMockPath('app/Providers/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($serviceProviderPath), ['Test Service Provider File Not Being Overwritten']);

        // Try to create a second ParentDirectory/MyServiceProvider
        $output = $this->executeCommand('ParentDirectory/MyServiceProvider');

        // Assert the default content service provider is always there
        $this->assertStringContainsString('Test Service Provider File Not Being Overwritten', $this->getMockFileContents($serviceProviderPath));
        // Assert we see an error message
        $this->assertStringContainsString('Provider MyServiceProvider already exists.', $output);
    }

    protected function executeCommand($name): string
    {
        $app = $this->appWithMockBasePath();

        $command = new ServiceProviderMakeCommand($app);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['name' => $name]);

        return $commandTester->getDisplay();
    }
}
