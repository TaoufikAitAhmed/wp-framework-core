<?php

namespace themes\Wordpress\Framework\Core\Test\Commands;

use themes\Wordpress\Framework\Core\Artisan\Commands\TaxonomyMakeCommand;
use themes\Wordpress\Framework\Core\Term;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use phpmock\MockBuilder;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TaxonomyMakeTest extends CommandMakerTestCase
{
    public function testItCanCreateATaxonomy()
    {
        $output = $this->executeCommand('ProductCategory', [
            ' ', // Plural
            ' ', // WordPress Term Name
            ' ', // Slug
            ' ', // Auto Register?
        ]);

        // Assert the file was created
        $relativePath = 'app/Taxonomies/ProductCategory.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Taxonomies\ProductCategory::class);
        $this->assertTrue($class->isSubclassOf(Term::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php

                namespace App\Taxonomies;

                use themes\Wordpress\Framework\Core\Term;

                class ProductCategory extends Term
                {

                    /**
                     * Return the key used to register the taxonomy with WordPress
                     *
                     * First parameter of the `register_taxonomy` function:
                     * https://developer.wordpress.org/reference/functions/register_taxonomy/
                     *
                     * @return string
                     */
                    public static function getTaxonomyType(): string
                    {
                        return 'product_category';
                    }

                    /**
                     * Return the object type which use this taxonomy.
                     *
                     * Second parameter of the `register_taxonomy` function:
                     * https://developer.wordpress.org/reference/functions/register_taxonomy/
                     *
                     * @return array|null
                     */
                    public static function getTaxonomyObjectTypes(): ?array
                    {
                        return ['post'];
                    }

                    /**
                     * Return the config to use to register the taxonomy with WordPress
                     *
                     * Third parameter of the `register_taxonomy` function:
                     * https://developer.wordpress.org/reference/functions/register_taxonomy/
                     *
                     * @return array|null
                     */
                    protected static function getTaxonomyConfig(): ?array
                    {
                        return [
                            'labels'      => [
                                'name'          => __('Product categories'),
                                'singular_name' => __('Product category'),
                            ],
                            'public'      => true,
                            'has_archive' => true,
                            'rewrite'     => [
                                'slug' => 'product-category',
                            ],
                        ];
                    }

                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Registered
        $config = $this->requireMockFile('config/taxonomies.php');
        $this->assertContains(\App\Taxonomies\ProductCategory::class, $config['register']);

        // Assert we have the message that tell the user the Taxonomy was successfully composed.
        $this->assertStringContainsString('ProductCategory Taxonomy successfully composed.', $output);

        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Taxonomies/ProductCategory.php', $output);
        $this->assertStringContainsString('config/taxonomies.php', $output);
    }

    public function testItCanCreateATaxonomyAndChangePlural()
    {
        $this->executeCommand('ProductCategory', [
            'Pluralized', // Plural
            ' ', // WordPress Term Name
            ' ', // Slug
            ' ', // Auto Register?
        ]);

        $relativePath = 'app/Taxonomies/ProductCategory.php';
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Taxonomies\ProductCategory::class);
        $data = $this->getTaxonomyConfig($class);
        $this->assertSame('Pluralized', $data['labels']['name']);
    }

    public function testItCanCreateATaxonomyAndChangeWpTaxonomyName()
    {
        $this->executeCommand('ProductCategory', [
            ' ', // Plural
            'wp-term-name', // WordPress Post Name
            ' ', // Slug
            ' ', // Auto Register?
        ]);

        $relativePath = 'app/Taxonomies/ProductCategory.php';
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $this->assertSame('wp-term-name', \App\Taxonomies\ProductCategory::getTaxonomyType());
    }

    public function testItCanCreateATaxonomyAndChangeSlug()
    {
        $this->executeCommand('ProductCategory', [
            ' ', // Plural
            ' ', // WordPress Term name
            'wp-slug', // Slug
            ' ', // Auto Register?
        ]);

        $relativePath = 'app/Taxonomies/ProductCategory.php';
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Taxonomies\ProductCategory::class);
        $data = $this->getTaxonomyConfig($class);
        $this->assertSame('wp-slug', $data['rewrite']['slug']);
    }

    public function testItCanCreateATaxonomyAndNotRegisterWithConfig()
    {
        $this->executeCommand('ProductCategory', [
            ' ', // Plural
            ' ', // WordPress Term Name
            ' ', // Slug
            'n', // Auto Register?
        ]);

        // Assert the file was created
        $relativePath = 'app/Taxonomies/ProductCategory.php';
        $this->requireMockFile($relativePath);

        // Registered
        $config = $this->requireMockFile('config/taxonomies.php');
        $this->assertNotContains(\App\Taxonomies\ProductCategory::class, $config['register']);
    }

    public function testItDoesNotCreateATaxonomyIfOneAlreadyExists()
    {
        // Create ProductCategory file
        $postTypePath = 'app/Taxonomies/ProductCategory.php';
        mkdir($this->getMockPath('app/Taxonomies'), 0777, true);
        file_put_contents($this->getMockPath($postTypePath), ['Test Taxonomy File Not Being Overwritten']);

        // Try to create a second ProductCategory
        $output = $this->executeCommand('ProductCategory', [
            ' ', // Plural
            ' ', // WordPress Term Name
            ' ', // Slug
            ' ', // Auto Register?
        ]);

        // Assert the default content post type is always there
        $this->assertStringContainsString('Test Taxonomy File Not Being Overwritten', $this->getMockFileContents($postTypePath));
        // Assert we see an error message
        $this->assertStringContainsString('Taxonomy ProductCategory already exists.', $output);
    }

    public function testItIsPossibleToCreateATaxonomyInASubFolder()
    {
        $output = $this->executeCommand('ParentDirectory/ProductCategory', [
            ' ', // Plural
            ' ', // WordPress Term Name
            ' ', // Slug
            ' ', // Auto Register?
        ]);

        // Assert the file was created
        $relativePath = 'app/Taxonomies/ParentDirectory/ProductCategory.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Taxonomies\ParentDirectory\ProductCategory::class);
        $this->assertTrue($class->isSubclassOf(Term::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php

                namespace App\Taxonomies\ParentDirectory;

                use themes\Wordpress\Framework\Core\Term;

                class ProductCategory extends Term
                {

                    /**
                     * Return the key used to register the taxonomy with WordPress
                     *
                     * First parameter of the `register_taxonomy` function:
                     * https://developer.wordpress.org/reference/functions/register_taxonomy/
                     *
                     * @return string
                     */
                    public static function getTaxonomyType(): string
                    {
                        return 'product_category';
                    }

                    /**
                     * Return the object type which use this taxonomy.
                     *
                     * Second parameter of the `register_taxonomy` function:
                     * https://developer.wordpress.org/reference/functions/register_taxonomy/
                     *
                     * @return array|null
                     */
                    public static function getTaxonomyObjectTypes(): ?array
                    {
                        return ['post'];
                    }

                    /**
                     * Return the config to use to register the taxonomy with WordPress
                     *
                     * Third parameter of the `register_taxonomy` function:
                     * https://developer.wordpress.org/reference/functions/register_taxonomy/
                     *
                     * @return array|null
                     */
                    protected static function getTaxonomyConfig(): ?array
                    {
                        return [
                            'labels'      => [
                                'name'          => __('Product categories'),
                                'singular_name' => __('Product category'),
                            ],
                            'public'      => true,
                            'has_archive' => true,
                            'rewrite'     => [
                                'slug' => 'product-category',
                            ],
                        ];
                    }

                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Registered
        $config = $this->requireMockFile('config/taxonomies.php');
        $this->assertContains(\App\Taxonomies\ParentDirectory\ProductCategory::class, $config['register']);

        // Assert we have the message that tell the user the Taxonomy was successfully composed.
        $this->assertStringContainsString('ProductCategory Taxonomy successfully composed.', $output);

        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Taxonomies/ParentDirectory/ProductCategory.php', $output);
        $this->assertStringContainsString('config/taxonomies.php', $output);
    }

    public function testItDoesNotCreateAPostTypeIfOneAlreadyExistsInASubFolder()
    {
        // Create ProductCategory file
        $postTypePath = 'app/Taxonomies/ParentDirectory/ProductCategory.php';
        mkdir($this->getMockPath('app/Taxonomies/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($postTypePath), ['Test Taxonomy File Not Being Overwritten']);

        // Try to create a second ProductCategory
        $output = $this->executeCommand('ParentDirectory/ProductCategory', [
            ' ', // Plural
            ' ', // WordPress Term Name
            ' ', // Slug
            ' ', // Auto Register?
        ]);

        // Assert the default content post type is always there
        $this->assertStringContainsString('Test Taxonomy File Not Being Overwritten', $this->getMockFileContents($postTypePath));
        // Assert we see an error message
        $this->assertStringContainsString('Taxonomy ProductCategory already exists.', $output);
    }

    protected function executeCommand($name, array $input)
    {
        $this->mockWordPressLanguageFunctions();
        $app = $this->appWithMockBasePath();
        mkdir($this->getMockPath('config'));
        file_put_contents(
            $this->getMockPath('config/taxonomies.php'),
            "<?php
return [
    /**
     * List all the sub-classes of themes\Wordpress\Framework\Core\Term in your app that you wish to
     * automatically register with WordPress as part of the bootstrap process.
     */
    'register' => [
    ],
];",
        );

        $command = new TaxonomyMakeCommand($app);
        $commandTester = new CommandTester($command);

        $commandTester->setInputs($input);

        $commandTester->execute(['name' => $name]);

        return $commandTester->getDisplay();
    }

    protected function getTaxonomyConfig(ReflectionClass $class)
    {
        $method = $class->getMethod('getTaxonomyConfig');
        $method->setAccessible(true);

        return $method->invoke(null);
    }

    protected function mockWordPressLanguageFunctions(string $namespace = 'App\Taxonomies')
    {
        $builder = new MockBuilder();
        $builder
            ->setNamespace($namespace)
            ->setName('__')
            ->setFunction(fn ($input) => $input);

        $mock = $builder->build();
        $mock->enable();
    }
}
