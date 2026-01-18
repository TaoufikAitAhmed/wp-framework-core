<?php

namespace themes\Wordpress\Framework\Core\Test\Unit\Artisan\Commands\Acf;

use themes\Wordpress\Framework\Core\Acf\Options;
use themes\Wordpress\Framework\Core\Artisan\Artisan;
use themes\Wordpress\Framework\Core\Artisan\Commands\Acf\OptionsMakeCommand;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use Rareloop\Lumberjack\Application;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class OptionsMakeTest extends CommandMakerTestCase
{
    public function test_command_is_registered_by_default()
    {
        $app = new Application();
        $kernal = new Artisan($app);

        $this->assertContains(OptionsMakeCommand::class, $kernal->defaultCommands());
    }

    public function test_it_can_create_acf_options()
    {
        $output = $this->executeCommand('MyAcfOptions');

        // Assert the file was created
        $relativePath = 'app/Acf/Options/MyAcfOptions.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Acf\Options\MyAcfOptions::class);
        $this->assertTrue($class->isSubclassOf(Options::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\Acf\Options;
                
                use themes\Wordpress\Framework\Core\Acf\Options as Field;
                use StoutLogic\AcfBuilder\FieldsBuilder;
                
                class MyAcfOptions extends Field
                {
                    /**
                     * The option page menu name.
                     *
                     * @var string
                     */
                    public string $name = 'MyAcfOptions';
                
                    /**
                     * The option page document title.
                     *
                     * @var string
                     */
                    public string $title = 'MyAcfOptions | Options';
                
                    /**
                     * The option page field group.
                     *
                     * @return array
                     * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
                     */
                    public function fields(): array
                    {
                        //@formatter:off
                        $myAcfOptions = new FieldsBuilder('my_acf_options');
                
                        $myAcfOptions
                            ->addRepeater('items')
                                ->addText('item')
                            ->endRepeater();
                
                        return $myAcfOptions->build();
                        //@formatter:on
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Options was successfully composed.
        $this->assertStringContainsString('MyAcfOptions ACF Options successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Acf/Options/MyAcfOptions.php', $output);
    }

    public function test_it_not_create_acf_options_if_acf_options_already_exists()
    {
        // Create MyAcfOptions file
        $acfOptionsPath = 'app/Acf/Options/MyAcfOptions.php';
        mkdir($this->getMockPath('app/Acf/Options'), 0777, true);
        file_put_contents($this->getMockPath($acfOptionsPath), ['Test Options File Not Being Overwritten']);

        // Try to create a second MyAcfOptions
        $output = $this->executeCommand('MyAcfOptions');

        // Assert the default content acf_options is always there
        $this->assertStringContainsString('Test Options File Not Being Overwritten', $this->getMockFileContents($acfOptionsPath));
        // Assert we see an error message
        $this->assertStringContainsString('Options MyAcfOptions already exists.', $output);
    }

    public function test_it_can_create_acf_options_in_sub_folder()
    {
        $output = $this->executeCommand('ParentDirectory/MyAcfOptions');

        // Assert the file was created
        $relativePath = 'app/Acf/Options/ParentDirectory/MyAcfOptions.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Acf\Options\ParentDirectory\MyAcfOptions::class);
        $this->assertTrue($class->isSubclassOf(Options::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\Acf\Options\ParentDirectory;
                
                use themes\Wordpress\Framework\Core\Acf\Options as Field;
                use StoutLogic\AcfBuilder\FieldsBuilder;
                
                class MyAcfOptions extends Field
                {
                    /**
                     * The option page menu name.
                     *
                     * @var string
                     */
                    public string $name = 'MyAcfOptions';
                
                    /**
                     * The option page document title.
                     *
                     * @var string
                     */
                    public string $title = 'MyAcfOptions | Options';
                
                    /**
                     * The option page field group.
                     *
                     * @return array
                     * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
                     */
                    public function fields(): array
                    {
                        //@formatter:off
                        $myAcfOptions = new FieldsBuilder('my_acf_options');
                
                        $myAcfOptions
                            ->addRepeater('items')
                                ->addText('item')
                            ->endRepeater();
                
                        return $myAcfOptions->build();
                        //@formatter:on
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Options was successfully composed.
        $this->assertStringContainsString('MyAcfOptions ACF Options successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Acf/Options/ParentDirectory/MyAcfOptions.php', $output);
    }

    public function test_it_does_not_create_acf_options_if_one_already_exists_in_sub_folder()
    {
        // Create ParentDirectory/MyAcfOptions file
        $acfOptionsPath = 'app/Acf/Options/ParentDirectory/MyAcfOptions.php';
        mkdir($this->getMockPath('app/Acf/Options/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($acfOptionsPath), ['Test Options File Not Being Overwritten']);

        // Try to create a second ParentDirectory/MyAcfOptions
        $output = $this->executeCommand('ParentDirectory/MyAcfOptions');

        // Assert the default content acf_options is always there
        $this->assertStringContainsString('Test Options File Not Being Overwritten', $this->getMockFileContents($acfOptionsPath));
        // Assert we see an error message
        $this->assertStringContainsString('Options MyAcfOptions already exists.', $output);
    }

    public function test_it_can_create_an_acf_option_with_full_argument()
    {
        $output = $this->executeCommand('MyAcfOptions', [
            '--full' => true,
        ]);

        // Assert the file was created
        $relativePath = 'app/Acf/Options/MyAcfOptions.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Acf\Options\MyAcfOptions::class);
        $this->assertTrue($class->isSubclassOf(Options::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\Acf\Options;
                
                use themes\Wordpress\Framework\Core\Acf\Options as Field;
                use StoutLogic\AcfBuilder\FieldsBuilder;
                
                class MyAcfOptions extends Field
                {
                    /**
                     * The option page menu name.
                     *
                     * @var string
                     */
                    public string $name = 'MyAcfOptions';
                
                    /**
                     * The option page menu slug.
                     *
                     * @var string
                     */
                    public string $slug = 'my-acf-options';
                
                    /**
                     * The option page document title.
                     *
                     * @var string
                     */
                    public string $title = 'MyAcfOptions | Options';
                
                    /**
                     * The option page permission capability.
                     *
                     * @var string
                     */
                    public string $capability = 'edit_theme_options';
                
                    /**
                     * The option page menu position.
                     *
                     * @var int|string
                     */
                    public $position = '';
                
                    /**
                     * The slug of another admin page to be used as a parent.
                     *
                     * @var string|null
                     */
                    public ?string $parent = null;
                
                    /**
                     * The option page menu icon.
                     *
                     * @var string|null
                     */
                    public ?string $icon = null;
                
                    /**
                     * Redirect to the first child page if one exists.
                     *
                     * @var bool
                     */
                    public bool $redirect = true;
                
                    /**
                     * The post ID to save and load values from.
                     *
                     * @var string|int
                     */
                    public $post = 'options';
                
                    /**
                     * The option page autoload setting.
                     *
                     * @var bool
                     */
                    public bool $autoload = true;
                
                    /**
                     * Localized text displayed on the submit button.
                     *
                     * @return string
                     */
                    public function updateButton(): string
                    {
                        return __('Update', 'acf');
                    }
                
                    /**
                     * Localized text displayed after form submission.
                     *
                     * @return string
                     */
                    public function updatedMessage(): string
                    {
                        return __('MyAcfOptions Updated', 'acf');
                    }
                
                    /**
                     * The option page field group.
                     *
                     * @return array
                     * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
                     */
                    public function fields(): array
                    {
                        //@formatter:off
                        $myAcfOptions = new FieldsBuilder('my_acf_options');
                
                        $myAcfOptions
                            ->addRepeater('items')
                                ->addText('item')
                            ->endRepeater();
                
                        return $myAcfOptions->build();
                        //@formatter:on
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Options was successfully composed.
        $this->assertStringContainsString('MyAcfOptions ACF Options successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Acf/Options/MyAcfOptions.php', $output);
    }

    protected function executeCommand($name, array $extra = []): string
    {
        $app = $this->appWithMockBasePath();

        $command = new OptionsMakeCommand($app);
        $commandTester = new CommandTester($command);

        $commandTester->execute(
            array_merge([
                'name' => $name,
            ], $extra)
        );

        return $commandTester->getDisplay();
    }
}
