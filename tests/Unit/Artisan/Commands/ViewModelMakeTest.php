<?php

namespace themes\Wordpress\Framework\Core\Test\Commands;

use themes\Wordpress\Framework\Core\Artisan\Commands\ViewModelMakeCommand;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use Rareloop\Lumberjack\ViewModel;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ViewModelMakeTest extends CommandMakerTestCase
{
    public function test_it_can_create_view_model()
    {
        $output = $this->executeCommand('MyViewModel');

        // Assert the file was created
        $relativePath = 'app/ViewModels/MyViewModel.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\ViewModels\MyViewModel::class);
        $this->assertTrue($class->isSubclassOf(ViewModel::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php
                
                namespace App\ViewModels;
                
                use Rareloop\Lumberjack\ViewModel;
                
                class MyViewModel extends ViewModel
                {
                    public function __construct()
                    {
                        //
                    }
                }
                
                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the View Model was successfully composed.
        $this->assertStringContainsString('MyViewModel View Model successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/ViewModels/MyViewModel.php', $output);
    }

    public function test_it_not_create_view_model_if_view_model_already_exists()
    {
        // Create MyViewModel file
        $viewModelPath = 'app/ViewModels/MyViewModel.php';
        mkdir($this->getMockPath('app/ViewModels'), 0777, true);
        file_put_contents($this->getMockPath($viewModelPath), ['Test View Model File Not Being Overwritten']);

        // Try to create a second MyViewModel
        $output = $this->executeCommand('MyViewModel');

        // Assert the default content view model is always there
        $this->assertStringContainsString('Test View Model File Not Being Overwritten', $this->getMockFileContents($viewModelPath));
        // Assert we see an error message
        $this->assertStringContainsString('View Model MyViewModel already exists.', $output);
    }

    public function test_it_can_create_view_model_in_sub_folder()
    {
        $output = $this->executeCommand('ParentDirectory/MyViewModel');

        // Assert the file was created
        $relativePath = 'app/ViewModels/ParentDirectory/MyViewModel.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\ViewModels\ParentDirectory\MyViewModel::class);
        $this->assertTrue($class->isSubclassOf(ViewModel::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php
                
                namespace App\ViewModels\ParentDirectory;
                
                use Rareloop\Lumberjack\ViewModel;
                
                class MyViewModel extends ViewModel
                {
                    public function __construct()
                    {
                        //
                    }
                }
                
                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the View Model was successfully composed.
        $this->assertStringContainsString('MyViewModel View Model successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/ViewModels/ParentDirectory/MyViewModel.php', $output);
    }

    public function test_it_does_not_create_view_model_if_one_already_exists_in_sub_folder()
    {
        // Create ParentDirectory/MyViewModel file
        $viewModelPath = 'app/ViewModels/ParentDirectory/MyViewModel.php';
        mkdir($this->getMockPath('app/ViewModels/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($viewModelPath), ['Test View Model File Not Being Overwritten']);

        // Try to create a second ParentDirectory/MyViewModel
        $output = $this->executeCommand('ParentDirectory/MyViewModel');

        // Assert the default content view model is always there
        $this->assertStringContainsString('Test View Model File Not Being Overwritten', $this->getMockFileContents($viewModelPath));
        // Assert we see an error message
        $this->assertStringContainsString('View Model MyViewModel already exists.', $output);
    }

    protected function executeCommand($name): string
    {
        $app = $this->appWithMockBasePath();

        $command = new ViewModelMakeCommand($app);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['name' => $name]);

        return $commandTester->getDisplay();
    }
}
