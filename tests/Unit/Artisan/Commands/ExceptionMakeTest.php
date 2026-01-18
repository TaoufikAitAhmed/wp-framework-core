<?php

namespace themes\Wordpress\Framework\Core\Test\Commands;

use themes\Wordpress\Framework\Core\Artisan\Commands\ExceptionMakeCommand;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use Exception;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ExceptionMakeTest extends CommandMakerTestCase
{
    public function test_it_can_create_exception()
    {
        $output = $this->executeCommand('MyException');

        // Assert the file was created
        $relativePath = 'app/Exceptions/MyException.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Exceptions\MyException::class);
        $this->assertTrue($class->isSubclassOf(Exception::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php
                
                namespace App\Exceptions;
                
                use Exception;
                
                class MyException extends Exception
                {
                
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Exception was successfully composed.
        $this->assertStringContainsString('MyException Exception successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Exceptions/MyException.php', $output);
    }

    public function test_it_not_create_exception_if_exception_already_exists()
    {
        // Create MyException file
        $exceptionPath = 'app/Exceptions/MyException.php';
        mkdir($this->getMockPath('app/Exceptions'), 0777, true);
        file_put_contents($this->getMockPath($exceptionPath), ['Test Exception File Not Being Overwritten']);

        // Try to create a second MyException
        $output = $this->executeCommand('MyException');

        // Assert the default content exception is always there
        $this->assertStringContainsString('Test Exception File Not Being Overwritten', $this->getMockFileContents($exceptionPath));
        // Assert we see an error message
        $this->assertStringContainsString('Exception MyException already exists.', $output);
    }

    public function test_it_can_create_exception_in_sub_folder()
    {
        $output = $this->executeCommand('ParentDirectory/MyException');

        // Assert the file was created
        $relativePath = 'app/Exceptions/ParentDirectory/MyException.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Exceptions\ParentDirectory\MyException::class);
        $this->assertTrue($class->isSubclassOf(Exception::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php
                
                namespace App\Exceptions\ParentDirectory;
                
                use Exception;
                
                class MyException extends Exception
                {
                
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Exception was successfully composed.
        $this->assertStringContainsString('MyException Exception successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Exceptions/ParentDirectory/MyException.php', $output);
    }

    public function test_it_does_not_create_exception_if_one_already_exists_in_sub_folder()
    {
        // Create ParentDirectory/MyException file
        $exceptionPath = 'app/Exceptions/ParentDirectory/MyException.php';
        mkdir($this->getMockPath('app/Exceptions/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($exceptionPath), ['Test Exception File Not Being Overwritten']);

        // Try to create a second ParentDirectory/MyException
        $output = $this->executeCommand('ParentDirectory/MyException');

        // Assert the default content exception is always there
        $this->assertStringContainsString('Test Exception File Not Being Overwritten', $this->getMockFileContents($exceptionPath));
        // Assert we see an error message
        $this->assertStringContainsString('Exception MyException already exists.', $output);
    }

    protected function executeCommand($name): string
    {
        $app = $this->appWithMockBasePath();

        $command = new ExceptionMakeCommand($app);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['name' => $name]);

        return $commandTester->getDisplay();
    }
}
