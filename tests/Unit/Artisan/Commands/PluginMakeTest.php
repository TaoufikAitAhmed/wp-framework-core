<?php

namespace themes\Wordpress\Framework\Core\Test\Commands;

use themes\Wordpress\Framework\Core\Artisan\Commands\PluginMakeCommand;
use themes\Wordpress\Framework\Core\Plugins\Plugin;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PluginMakeTest extends CommandMakerTestCase
{
    public function testItCanCreateAPlugin()
    {
        $output = $this->executeCommand('MyPlugin');

        // Assert the file was created
        $relativePath = 'app/Plugins/MyPlugin.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Plugins\MyPlugin::class);
        $this->assertTrue($class->isSubclassOf(Plugin::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php

                namespace App\Plugins;

                use themes\Wordpress\Framework\Core\Plugins\Plugin;

                class MyPlugin extends Plugin
                {

                    /**
                     * Clean plugins assets (js, css, ...)
                     *
                     * @return void
                     */
                    public function clean(): void
                    {
                        //
                    }

                }
                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Registered
        $config = $this->requireMockFile('config/plugins.php');
        $this->assertContains(\App\Plugins\MyPlugin::class, $config);

        // Assert we have the message that tell the user the Plugin was successfully composed.
        $this->assertStringContainsString('MyPlugin Plugin successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Plugins/MyPlugin.php', $output);
        $this->assertStringContainsString('config/plugins.php', $output);
    }

    public function testItDoesNotCreateAPluginIfOneAlreadyExists()
    {
        // Create Product file
        $postTypePath = 'app/Plugins/MyPlugin.php';
        mkdir($this->getMockPath('app/Plugins'), 0777, true);
        file_put_contents($this->getMockPath($postTypePath), ['Test Plugin File Not Being Overwritten']);

        // Try to create a second MyPlugin
        $output = $this->executeCommand('MyPlugin');

        // Assert the default content post type is always there
        $this->assertStringContainsString('Test Plugin File Not Being Overwritten', $this->getMockFileContents($postTypePath));
        // Assert we see an error message
        $this->assertStringContainsString('Plugin MyPlugin already exists.', $output);
    }

    public function testDeveloperCanChooseToDontCleanThePluginByDefault()
    {
        $output = $this->executeCommand('MyPlugin', false);

        // Assert we see a test that ask to clean the plugin or not by default
        $this->assertStringContainsString('Do you want to clean this plugin by default ? (yes/no) [yes]', $output);

        // Not registered
        $config = $this->requireMockFile('config/plugins.php');
        $this->assertNotContains(\App\Plugins\MyPlugin::class, $config);

        // Assert we have the message that tell the user the Plugin was successfully composed.
        $this->assertStringContainsString('MyPlugin Plugin successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Plugins/MyPlugin.php', $output);
        $this->assertStringNotContainsString('config/plugins.php', $output);
    }

    public function testItIsPossibleToCreateAPluginInASubFolder()
    {
        $output = $this->executeCommand('ParentDirectory/MyPlugin');

        // Assert the file was created
        $relativePath = 'app/Plugins/ParentDirectory/MyPlugin.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Plugins\ParentDirectory\MyPlugin::class);
        $this->assertTrue($class->isSubclassOf(Plugin::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php

                namespace App\Plugins\ParentDirectory;

                use themes\Wordpress\Framework\Core\Plugins\Plugin;

                class MyPlugin extends Plugin
                {

                    /**
                     * Clean plugins assets (js, css, ...)
                     *
                     * @return void
                     */
                    public function clean(): void
                    {
                        //
                    }

                }
                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Registered
        $config = $this->requireMockFile('config/plugins.php');
        $this->assertContains(\App\Plugins\ParentDirectory\MyPlugin::class, $config);

        // Assert we have the message that tell the user the Plugin was successfully composed.
        $this->assertStringContainsString('MyPlugin Plugin successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Plugins/ParentDirectory/MyPlugin.php', $output);
        $this->assertStringContainsString('config/plugins.php', $output);
    }

    public function testItDoesNotCreateAPluginIfOneAlreadyExistsInASubFolder()
    {
        // Create Product file
        $postTypePath = 'app/Plugins/ParentDirectory/MyPlugin.php';
        mkdir($this->getMockPath('app/Plugins/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($postTypePath), ['Test Plugin File Not Being Overwritten']);

        // Try to create a second MyPlugin
        $output = $this->executeCommand('ParentDirectory/MyPlugin');

        // Assert the default content post type is always there
        $this->assertStringContainsString('Test Plugin File Not Being Overwritten', $this->getMockFileContents($postTypePath));
        // Assert we see an error message
        $this->assertStringContainsString('Plugin MyPlugin already exists.', $output);
    }

    protected function executeCommand($name, bool $autoRegister = true): string
    {
        $app = $this->appWithMockBasePath();
        mkdir($this->getMockPath('config'));
        file_put_contents(
            $this->getMockPath('config/plugins.php'),
            <<<CLASS
                <?php

                /*
                |--------------------------------------------------------------------------
                | Clean plugins
                |--------------------------------------------------------------------------
                |
                | List all the sub-classes of themes\Wordpress\Framework\Core\Plugins\Plugin
                | in your app that you wish to automatically clean (removing js, css etc...)
                | automatically
                |
                */

                return [
                    // App\Plugins\ContactForm7::class
                ];
                CLASS
            ,
        );

        $input = [];

        // Auto register ?
        $input[] = $autoRegister ? 'y' : 'n';

        $command = new PluginMakeCommand($app);
        $commandTester = new CommandTester($command);

        $commandTester->setInputs($input);

        $commandTester->execute(['name' => $name]);

        return $commandTester->getDisplay();
    }
}
