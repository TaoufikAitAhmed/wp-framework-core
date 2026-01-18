<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Artisan\Commands\Acf;

use themes\Wordpress\Framework\Core\Acf\Field;
use themes\Wordpress\Framework\Core\Artisan\Artisan;
use themes\Wordpress\Framework\Core\Artisan\Commands\Acf\PageMakeCommand;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use Rareloop\Lumberjack\Application;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PageMakeTest extends CommandMakerTestCase
{
    public function test_command_is_registered_by_default()
    {
        $app = new Application();
        $kernal = new Artisan($app);

        $this->assertContains(PageMakeCommand::class, $kernal->defaultCommands());
    }

    public function test_it_can_create_acf_page()
    {
        $output = $this->executeCommand('MyAcfPage');

        // Assert the file was created
        $relativePath = 'app/Acf/Pages/MyAcfPage.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Acf\Pages\MyAcfPage::class);
        $this->assertTrue($class->isSubclassOf(Field::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\Acf\Pages;
                
                use themes\Wordpress\Framework\Core\Acf\Field;
                use StoutLogic\AcfBuilder\FieldsBuilder;
                
                class MyAcfPage extends Field
                {
                    /**
                     * The field group.
                     *
                     * @return array
                     * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
                     */
                    public function fields(): array
                    {
                        //@formatter:off
                        $myAcfPage = new FieldsBuilder('my_acf_page');
                
                        $myAcfPage
                            ->setLocation('page_template', '==', 'page-templates/MyAcfPage.php');
                
                        $myAcfPage
                            ->addRepeater('items')
                                ->addText('item')
                            ->endRepeater();
                
                        return $myAcfPage->build();
                        //@formatter:on
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Field was successfully composed.
        $this->assertStringContainsString('MyAcfPage ACF Page successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Acf/Pages/MyAcfPage.php', $output);
    }

    public function test_it_not_create_acf_page_if_acf_page_already_exists()
    {
        // Create MyAcfPage file
        $acfFieldPath = 'app/Acf/Pages/MyAcfPage.php';
        mkdir($this->getMockPath('app/Acf/Pages'), 0777, true);
        file_put_contents($this->getMockPath($acfFieldPath), ['Test Field File Not Being Overwritten']);

        // Try to create a second MyAcfPage
        $output = $this->executeCommand('MyAcfPage');

        // Assert the default content acf_page is always there
        $this->assertStringContainsString('Test Field File Not Being Overwritten', $this->getMockFileContents($acfFieldPath));
        // Assert we see an error message
        $this->assertStringContainsString('ACF Page MyAcfPage already exists.', $output);
    }

    public function test_it_can_create_acf_page_in_sub_folder()
    {
        $output = $this->executeCommand('ParentDirectory/MyAcfPage');

        // Assert the file was created
        $relativePath = 'app/Acf/Pages/ParentDirectory/MyAcfPage.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Acf\Pages\ParentDirectory\MyAcfPage::class);
        $this->assertTrue($class->isSubclassOf(Field::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\Acf\Pages\ParentDirectory;
                
                use themes\Wordpress\Framework\Core\Acf\Field;
                use StoutLogic\AcfBuilder\FieldsBuilder;
                
                class MyAcfPage extends Field
                {
                    /**
                     * The field group.
                     *
                     * @return array
                     * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
                     */
                    public function fields(): array
                    {
                        //@formatter:off
                        $myAcfPage = new FieldsBuilder('my_acf_page');
                
                        $myAcfPage
                            ->setLocation('page_template', '==', 'page-templates/MyAcfPage.php');
                
                        $myAcfPage
                            ->addRepeater('items')
                                ->addText('item')
                            ->endRepeater();
                
                        return $myAcfPage->build();
                        //@formatter:on
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Field was successfully composed.
        $this->assertStringContainsString('MyAcfPage ACF Page successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Acf/Pages/ParentDirectory/MyAcfPage.php', $output);
    }

    public function test_it_does_not_create_acf_page_if_one_already_exists_in_sub_folder()
    {
        // Create ParentDirectory/MyAcfPage file
        $acfFieldPath = 'app/Acf/Pages/ParentDirectory/MyAcfPage.php';
        mkdir($this->getMockPath('app/Acf/Pages/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($acfFieldPath), ['Test Field File Not Being Overwritten']);

        // Try to create a second ParentDirectory/MyAcfPage
        $output = $this->executeCommand('ParentDirectory/MyAcfPage');

        // Assert the default content acf_page is always there
        $this->assertStringContainsString('Test Field File Not Being Overwritten', $this->getMockFileContents($acfFieldPath));
        // Assert we see an error message
        $this->assertStringContainsString('ACF Page MyAcfPage already exists.', $output);
    }

    protected function executeCommand($name): string
    {
        $app = $this->appWithMockBasePath();

        $command = new PageMakeCommand($app);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['name' => $name]);

        return $commandTester->getDisplay();
    }
}
