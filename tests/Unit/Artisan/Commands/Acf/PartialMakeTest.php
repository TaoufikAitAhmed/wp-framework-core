<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Artisan\Commands\Acf;

use themes\Wordpress\Framework\Core\Acf\Partial;
use themes\Wordpress\Framework\Core\Artisan\Artisan;
use themes\Wordpress\Framework\Core\Artisan\Commands\Acf\PartialMakeCommand;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use Rareloop\Lumberjack\Application;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PartialMakeTest extends CommandMakerTestCase
{
    public function test_command_is_registered_by_default()
    {
        $app = new Application();
        $kernal = new Artisan($app);

        $this->assertContains(PartialMakeCommand::class, $kernal->defaultCommands());
    }

    public function test_it_can_create_acf_partial()
    {
        $output = $this->executeCommand('MyAcfPartial');

        // Assert the file was created
        $relativePath = 'app/Acf/Partials/MyAcfPartial.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Acf\Partials\MyAcfPartial::class);
        $this->assertTrue($class->isSubclassOf(Partial::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\Acf\Partials;
                
                use themes\Wordpress\Framework\Core\Acf\Partial;
                use StoutLogic\AcfBuilder\FieldsBuilder;
                
                class MyAcfPartial extends Partial
                {
                    /**
                     * The field group.
                     *
                     * @return FieldsBuilder
                     * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
                     */
                    public function fields(): FieldsBuilder
                    {
                        //@formatter:off
                        $myAcfPartial = new FieldsBuilder('my_acf_partial');
                
                        $myAcfPartial
                            ->addRepeater('items')
                                ->addText('item')
                            ->endRepeater();
                
                        return $myAcfPartial;
                        //@formatter:on
                    }
                }
                
                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Field was successfully composed.
        $this->assertStringContainsString('MyAcfPartial ACF Partial successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Acf/Partials/MyAcfPartial.php', $output);
    }

    public function test_it_not_create_acf_partial_if_acf_partial_already_exists()
    {
        // Create MyAcfPartial file
        $acfPartialPath = 'app/Acf/Partials/MyAcfPartial.php';
        mkdir($this->getMockPath('app/Acf/Partials'), 0777, true);
        file_put_contents($this->getMockPath($acfPartialPath), ['Test Partial File Not Being Overwritten']);

        // Try to create a second MyAcfPartial
        $output = $this->executeCommand('MyAcfPartial');

        // Assert the default content acf_partial is always there
        $this->assertStringContainsString('Test Partial File Not Being Overwritten', $this->getMockFileContents($acfPartialPath));
        // Assert we see an error message
        $this->assertStringContainsString('ACF Partial MyAcfPartial already exists.', $output);
    }

    public function test_it_can_create_acf_partial_in_sub_folder()
    {
        $output = $this->executeCommand('ParentDirectory/MyAcfPartial');

        // Assert the file was created
        $relativePath = 'app/Acf/Partials/ParentDirectory/MyAcfPartial.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Acf\Partials\ParentDirectory\MyAcfPartial::class);
        $this->assertTrue($class->isSubclassOf(Partial::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\Acf\Partials\ParentDirectory;
                
                use themes\Wordpress\Framework\Core\Acf\Partial;
                use StoutLogic\AcfBuilder\FieldsBuilder;
                
                class MyAcfPartial extends Partial
                {
                    /**
                     * The field group.
                     *
                     * @return FieldsBuilder
                     * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
                     */
                    public function fields(): FieldsBuilder
                    {
                        //@formatter:off
                        $myAcfPartial = new FieldsBuilder('my_acf_partial');
                
                        $myAcfPartial
                            ->addRepeater('items')
                                ->addText('item')
                            ->endRepeater();
                
                        return $myAcfPartial;
                        //@formatter:on
                    }
                }
                
                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Field was successfully composed.
        $this->assertStringContainsString('MyAcfPartial ACF Partial successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Acf/Partials/ParentDirectory/MyAcfPartial.php', $output);
    }

    public function test_it_does_not_create_acf_partial_if_one_already_exists_in_sub_folder()
    {
        // Create ParentDirectory/MyAcfPartial file
        $acfPartialPath = 'app/Acf/Partials/ParentDirectory/MyAcfPartial.php';
        mkdir($this->getMockPath('app/Acf/Partials/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($acfPartialPath), ['Test Partial File Not Being Overwritten']);

        // Try to create a second ParentDirectory/MyAcfPartial
        $output = $this->executeCommand('ParentDirectory/MyAcfPartial');

        // Assert the default content acf_partial is always there
        $this->assertStringContainsString('Test Partial File Not Being Overwritten', $this->getMockFileContents($acfPartialPath));
        // Assert we see an error message
        $this->assertStringContainsString('ACF Partial MyAcfPartial already exists.', $output);
    }

    protected function executeCommand($name): string
    {
        $app = $this->appWithMockBasePath();

        $command = new PartialMakeCommand($app);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['name' => $name]);

        return $commandTester->getDisplay();
    }
}
