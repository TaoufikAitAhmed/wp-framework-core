<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Artisan\Commands\Acf;

use themes\Wordpress\Framework\Core\Acf\Field;
use themes\Wordpress\Framework\Core\Artisan\Artisan;
use themes\Wordpress\Framework\Core\Artisan\Commands\Acf\FieldMakeCommand;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use Rareloop\Lumberjack\Application;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class FieldMakeTest extends CommandMakerTestCase
{
    public function test_command_is_registered_by_default()
    {
        $app = new Application();
        $kernal = new Artisan($app);

        $this->assertContains(FieldMakeCommand::class, $kernal->defaultCommands());
    }

    public function test_it_can_create_acf_field()
    {
        $output = $this->executeCommand('MyAcfField');

        // Assert the file was created
        $relativePath = 'app/Acf/Fields/MyAcfField.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Acf\Fields\MyAcfField::class);
        $this->assertTrue($class->isSubclassOf(Field::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\Acf\Fields;
                
                use themes\Wordpress\Framework\Core\Acf\Field;
                use StoutLogic\AcfBuilder\FieldsBuilder;
                
                class MyAcfField extends Field
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
                        $myAcfField = new FieldsBuilder('my_acf_field');
                
                        $myAcfField
                            ->setLocation('post_type', '==', 'post');
                
                        $myAcfField
                            ->addRepeater('items')
                                ->addText('item')
                            ->endRepeater();
                
                        return $myAcfField->build();
                        //@formatter:on
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Field was successfully composed.
        $this->assertStringContainsString('MyAcfField ACF Field successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Acf/Fields/MyAcfField.php', $output);
    }

    public function test_it_not_create_acf_field_if_acf_field_already_exists()
    {
        // Create MyAcfField file
        $acfFieldPath = 'app/Acf/Fields/MyAcfField.php';
        mkdir($this->getMockPath('app/Acf/Fields'), 0777, true);
        file_put_contents($this->getMockPath($acfFieldPath), ['Test Field File Not Being Overwritten']);

        // Try to create a second MyAcfField
        $output = $this->executeCommand('MyAcfField');

        // Assert the default content acf_field is always there
        $this->assertStringContainsString('Test Field File Not Being Overwritten', $this->getMockFileContents($acfFieldPath));
        // Assert we see an error message
        $this->assertStringContainsString('Field MyAcfField already exists.', $output);
    }

    public function test_it_can_create_acf_field_in_sub_folder()
    {
        $output = $this->executeCommand('ParentDirectory/MyAcfField');

        // Assert the file was created
        $relativePath = 'app/Acf/Fields/ParentDirectory/MyAcfField.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Acf\Fields\ParentDirectory\MyAcfField::class);
        $this->assertTrue($class->isSubclassOf(Field::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\Acf\Fields\ParentDirectory;
                
                use themes\Wordpress\Framework\Core\Acf\Field;
                use StoutLogic\AcfBuilder\FieldsBuilder;
                
                class MyAcfField extends Field
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
                        $myAcfField = new FieldsBuilder('my_acf_field');
                
                        $myAcfField
                            ->setLocation('post_type', '==', 'post');
                
                        $myAcfField
                            ->addRepeater('items')
                                ->addText('item')
                            ->endRepeater();
                
                        return $myAcfField->build();
                        //@formatter:on
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Field was successfully composed.
        $this->assertStringContainsString('MyAcfField ACF Field successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Acf/Fields/ParentDirectory/MyAcfField.php', $output);
    }

    public function test_it_does_not_create_acf_field_if_one_already_exists_in_sub_folder()
    {
        // Create ParentDirectory/MyAcfField file
        $acfFieldPath = 'app/Acf/Fields/ParentDirectory/MyAcfField.php';
        mkdir($this->getMockPath('app/Acf/Fields/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($acfFieldPath), ['Test Field File Not Being Overwritten']);

        // Try to create a second ParentDirectory/MyAcfField
        $output = $this->executeCommand('ParentDirectory/MyAcfField');

        // Assert the default content acf_field is always there
        $this->assertStringContainsString('Test Field File Not Being Overwritten', $this->getMockFileContents($acfFieldPath));
        // Assert we see an error message
        $this->assertStringContainsString('Field MyAcfField already exists.', $output);
    }

    protected function executeCommand($name): string
    {
        $app = $this->appWithMockBasePath();

        $command = new FieldMakeCommand($app);
        $commandTester = new CommandTester($command);

        $commandTester->execute(['name' => $name]);

        return $commandTester->getDisplay();
    }
}
