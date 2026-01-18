<?php

namespace themes\Wordpress\Framework\Core\Test\Commands;

use themes\Wordpress\Framework\Core\Artisan\Commands\PostTypeMakeCommand;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use phpmock\MockBuilder;
use Rareloop\Lumberjack\Post;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * TODO : Refactor
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PostTypeMakeTest extends CommandMakerTestCase
{
    public function test_it_can_create_post_type()
    {
        $this->executeCommand('ProductSample', [
            ' ', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            ' ', // Features
            ' ', // Auto Register?
        ]);

        // Assert the file was created
        $relativePath = 'app/PostTypes/ProductSample.php';
        $this->assertMockPath($relativePath);
        $this->assertStringNotContainsString('DummyPostType', $this->getMockFileContents($relativePath));
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\PostTypes\ProductSample::class);
        $this->assertTrue($class->isSubclassOf(Post::class));
        $this->assertSame('product_sample', \App\PostTypes\ProductSample::getPostType());

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\PostTypes;
                
                use Rareloop\Lumberjack\Post;
                
                class ProductSample extends Post
                {
                    /**
                     * Return the key used to register the post type with WordPress
                     * First parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return string
                     */
                    public static function getPostType()
                    {
                        return 'product_sample';
                    }
                
                    /**
                     * Return the config to use to register the post type with WordPress
                     * Second parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return array
                     */
                    protected static function getPostTypeConfig()
                    {
                        return [
                            'labels' => [
                                'name' => __('Product samples'),
                                'singular_name' => __('Product sample'),
                            ],
                            'public' => true,
                            'has_archive' => true,
                            'supports' => [
                                'title',
                                'editor',
                                'thumbnail',
                                'author',
                                'revisions',
                            ],
                            'rewrite' => [
                                'slug' => 'product-sample',
                            ],
                        ];
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Registered
        $config = $this->requireMockFile('config/posttypes.php');
        $this->assertContains(\App\PostTypes\ProductSample::class, $config['register']);
    }

    public function test_it_can_create_post_type_and_change_plural()
    {
        $this->executeCommand('Product', [
            'Pluralized', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            ' ', // Features
            ' ', // Auto Register?
        ]);

        $relativePath = 'app/PostTypes/Product.php';
        $this->requireMockFile($relativePath);

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\PostTypes;
                
                use Rareloop\Lumberjack\Post;
                
                class Product extends Post
                {
                    /**
                     * Return the key used to register the post type with WordPress
                     * First parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return string
                     */
                    public static function getPostType()
                    {
                        return 'product';
                    }
                
                    /**
                     * Return the config to use to register the post type with WordPress
                     * Second parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return array
                     */
                    protected static function getPostTypeConfig()
                    {
                        return [
                            'labels' => [
                                'name' => __('Pluralized'),
                                'singular_name' => __('Product'),
                            ],
                            'public' => true,
                            'has_archive' => true,
                            'supports' => [
                                'title',
                                'editor',
                                'thumbnail',
                                'author',
                                'revisions',
                            ],
                            'rewrite' => [
                                'slug' => 'product',
                            ],
                        ];
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );
    }

    public function test_it_can_create_post_type_and_change_wp_post_name()
    {
        $this->executeCommand('Product', [
            ' ', // Plural
            'wp-post-name', // WordPress Post Name
            ' ', // Slug
            ' ', // Features
            ' ', // Auto Register?
        ]);

        $relativePath = 'app/PostTypes/Product.php';
        $this->requireMockFile($relativePath);

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\PostTypes;
                
                use Rareloop\Lumberjack\Post;
                
                class Product extends Post
                {
                    /**
                     * Return the key used to register the post type with WordPress
                     * First parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return string
                     */
                    public static function getPostType()
                    {
                        return 'wp-post-name';
                    }
                
                    /**
                     * Return the config to use to register the post type with WordPress
                     * Second parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return array
                     */
                    protected static function getPostTypeConfig()
                    {
                        return [
                            'labels' => [
                                'name' => __('Products'),
                                'singular_name' => __('Product'),
                            ],
                            'public' => true,
                            'has_archive' => true,
                            'supports' => [
                                'title',
                                'editor',
                                'thumbnail',
                                'author',
                                'revisions',
                            ],
                            'rewrite' => [
                                'slug' => 'product',
                            ],
                        ];
                    }
                }
                
                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );
    }

    public function test_it_can_create_post_type_and_change_slug()
    {
        $this->executeCommand('Product', [
            ' ', // Plural
            ' ', // WordPress Post Name
            'wp-slug', // Slug
            ' ', // Features
            ' ', // Auto Register?
        ]);

        $relativePath = 'app/PostTypes/Product.php';
        $this->requireMockFile($relativePath);

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\PostTypes;
                
                use Rareloop\Lumberjack\Post;
                
                class Product extends Post
                {
                    /**
                     * Return the key used to register the post type with WordPress
                     * First parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return string
                     */
                    public static function getPostType()
                    {
                        return 'product';
                    }
                
                    /**
                     * Return the config to use to register the post type with WordPress
                     * Second parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return array
                     */
                    protected static function getPostTypeConfig()
                    {
                        return [
                            'labels' => [
                                'name' => __('Products'),
                                'singular_name' => __('Product'),
                            ],
                            'public' => true,
                            'has_archive' => true,
                            'supports' => [
                                'title',
                                'editor',
                                'thumbnail',
                                'author',
                                'revisions',
                            ],
                            'rewrite' => [
                                'slug' => 'wp-slug',
                            ],
                        ];
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );
    }

    public function test_it_can_create_post_type_and_disable_archives()
    {
        $this->executeCommand('Product', [
            ' ', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            '0,1,2', // Features
            ' ', // Auto Register?
        ]);

        $relativePath = 'app/PostTypes/Product.php';
        $this->requireMockFile($relativePath);

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\PostTypes;
                
                use Rareloop\Lumberjack\Post;
                
                class Product extends Post
                {
                    /**
                     * Return the key used to register the post type with WordPress
                     * First parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return string
                     */
                    public static function getPostType()
                    {
                        return 'product';
                    }
                
                    /**
                     * Return the config to use to register the post type with WordPress
                     * Second parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return array
                     */
                    protected static function getPostTypeConfig()
                    {
                        return [
                            'labels' => [
                                'name' => __('Products'),
                                'singular_name' => __('Product'),
                            ],
                            'public' => true,
                            'has_archive' => false,
                            'supports' => [
                                'title',
                                'editor',
                                'thumbnail',
                                'author',
                                'revisions',
                            ],
                            'rewrite' => [
                                'slug' => 'product',
                            ],
                        ];
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );
    }

    public function test_it_can_create_post_type_and_disable_content_editor()
    {
        $this->executeCommand('Product', [
            ' ', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            '1,2,3', // Features
            ' ', // Auto Register?
        ]);

        $relativePath = 'app/PostTypes/Product.php';
        $this->requireMockFile($relativePath);

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php

                namespace App\PostTypes;
                
                use Rareloop\Lumberjack\Post;
                
                class Product extends Post
                {
                    /**
                     * Return the key used to register the post type with WordPress
                     * First parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return string
                     */
                    public static function getPostType()
                    {
                        return 'product';
                    }
                
                    /**
                     * Return the config to use to register the post type with WordPress
                     * Second parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return array
                     */
                    protected static function getPostTypeConfig()
                    {
                        return [
                            'labels' => [
                                'name' => __('Products'),
                                'singular_name' => __('Product'),
                            ],
                            'public' => true,
                            'has_archive' => true,
                            'supports' => [
                                'title',
                                                'thumbnail',
                                'author',
                                'revisions',
                            ],
                            'rewrite' => [
                                'slug' => 'product',
                            ],
                        ];
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );
    }

    public function test_it_can_create_post_type_and_disable_revisions()
    {
        $this->executeCommand('Product', [
            ' ', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            '0,1,3', // Features
            ' ', // Auto Register?
        ]);

        $relativePath = 'app/PostTypes/Product.php';
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\PostTypes\Product::class);
        $data = $this->getPostTypeConfig($class);
        $this->assertNotContains('revisions', $data['supports']);
    }

    public function test_it_can_create_post_type_and_disable_thumbnails()
    {
        $this->executeCommand('Product', [
            ' ', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            '0,2,3', // Features
            ' ', // Auto Register?
        ]);

        $relativePath = 'app/PostTypes/Product.php';
        $this->requireMockFile($relativePath);

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\PostTypes;
                
                use Rareloop\Lumberjack\Post;
                
                class Product extends Post
                {
                    /**
                     * Return the key used to register the post type with WordPress
                     * First parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return string
                     */
                    public static function getPostType()
                    {
                        return 'product';
                    }
                
                    /**
                     * Return the config to use to register the post type with WordPress
                     * Second parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return array
                     */
                    protected static function getPostTypeConfig()
                    {
                        return [
                            'labels' => [
                                'name' => __('Products'),
                                'singular_name' => __('Product'),
                            ],
                            'public' => true,
                            'has_archive' => true,
                            'supports' => [
                                'title',
                                'editor',
                                                'author',
                                'revisions',
                            ],
                            'rewrite' => [
                                'slug' => 'product',
                            ],
                        ];
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );
    }

    public function test_it_can_create_post_type_and_not_register_with_config()
    {
        $this->executeCommand('Product', [
            ' ', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            ' ', // Features
            'n', // Auto Register?
        ]);

        // Assert the file was created
        $relativePath = 'app/PostTypes/Product.php';
        $this->requireMockFile($relativePath);

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\PostTypes;
                
                use Rareloop\Lumberjack\Post;
                
                class Product extends Post
                {
                    /**
                     * Return the key used to register the post type with WordPress
                     * First parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return string
                     */
                    public static function getPostType()
                    {
                        return 'product';
                    }
                
                    /**
                     * Return the config to use to register the post type with WordPress
                     * Second parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return array
                     */
                    protected static function getPostTypeConfig()
                    {
                        return [
                            'labels' => [
                                'name' => __('Products'),
                                'singular_name' => __('Product'),
                            ],
                            'public' => true,
                            'has_archive' => true,
                            'supports' => [
                                'title',
                                'editor',
                                'thumbnail',
                                'author',
                                'revisions',
                            ],
                            'rewrite' => [
                                'slug' => 'product',
                            ],
                        ];
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );
    }

    public function test_the_user_sees_success_message_when_he_has_created_post_type()
    {
        $output = $this->executeCommand('Product', [
            ' ', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            ' ', // Features
            ' ', // Auto Register?
        ]);

        // Assert we have the message that tell the user the Post Type was successfully composed.
        $this->assertStringContainsString('Product Post Type successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/PostTypes/Product.php', $output);
    }

    public function test_it_does_not_create_post_type_if_one_already_exists()
    {
        // Create Product file
        $postTypePath = 'app/PostTypes/Product.php';
        mkdir($this->getMockPath('app/PostTypes'), 0777, true);
        file_put_contents($this->getMockPath($postTypePath), ['Test Post Type File Not Being Overwritten']);

        // Try to create a second Product
        $this->executeCommand('Product', [
            ' ', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            ' ', // Features
            ' ', // Auto Register?
        ]);

        // Assert the default content post type is always there
        $this->assertStringContainsString('Test Post Type File Not Being Overwritten', $this->getMockFileContents($postTypePath));
    }

    public function test_the_user_sees_an_error_message_if_he_tries_to_create_post_type_that_already_exists()
    {
        // Create Product file
        $postTypePath = 'app/PostTypes/Product.php';
        mkdir($this->getMockPath('app/PostTypes'), 0777, true);
        file_put_contents($this->getMockPath($postTypePath), ['Test Post Type File Not Being Overwritten']);

        // Try to create a second Product
        $output = $this->executeCommand('Product', [
            ' ', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            ' ', // Features
            ' ', // Auto Register?
        ]);

        // Assert we see an error message
        $this->assertStringContainsString('Post Type Product already exists.', $output);
    }

    public function test_it_is_possible_to_create_post_type_in_sub_folder()
    {
        $this->mockWordPressLanguageFunctions('App\PostTypes\ParentDirectory');
        $this->executeCommand('ParentDirectory/Product', [
            ' ', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            ' ', // Features
            ' ', // Auto Register?
        ]);

        // Assert the file was created
        $relativePath = 'app/PostTypes/ParentDirectory/Product.php';
        $this->assertMockPath($relativePath);
        $this->assertStringNotContainsString('DummyPostType', $this->getMockFileContents($relativePath));
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\PostTypes\ParentDirectory\Product::class);
        $this->assertTrue($class->isSubclassOf(Post::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                namespace App\PostTypes\ParentDirectory;
                
                use Rareloop\Lumberjack\Post;
                
                class Product extends Post
                {
                    /**
                     * Return the key used to register the post type with WordPress
                     * First parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return string
                     */
                    public static function getPostType()
                    {
                        return 'product';
                    }
                
                    /**
                     * Return the config to use to register the post type with WordPress
                     * Second parameter of the `register_post_type` function:
                     * https://codex.wordpress.org/Function_Reference/register_post_type
                     *
                     * @return array
                     */
                    protected static function getPostTypeConfig()
                    {
                        return [
                            'labels' => [
                                'name' => __('Products'),
                                'singular_name' => __('Product'),
                            ],
                            'public' => true,
                            'has_archive' => true,
                            'supports' => [
                                'title',
                                'editor',
                                'thumbnail',
                                'author',
                                'revisions',
                            ],
                            'rewrite' => [
                                'slug' => 'product',
                            ],
                        ];
                    }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Registered
        $config = $this->requireMockFile('config/posttypes.php');
        $this->assertContains(\App\PostTypes\ParentDirectory\Product::class, $config['register']);
    }

    public function test_the_user_sees_success_message_when_he_has_created_post_type_in_sub_folder()
    {
        $output = $this->executeCommand('ParentDirectory/Product', [
            ' ', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            ' ', // Features
            ' ', // Auto Register?
        ]);

        // Assert we have the message that tell the user the Post Type was successfully composed.
        $this->assertStringContainsString('Product Post Type successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/PostTypes/ParentDirectory/Product.php', $output);
    }

    public function test_it_does_not_create_post_type_if_one_already_exists_in_sub_folder()
    {
        // Create Product file
        $postTypePath = 'app/PostTypes/ParentDirectory/Product.php';
        mkdir($this->getMockPath('app/PostTypes/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($postTypePath), ['Test Post Type File Not Being Overwritten']);

        // Try to create a second Product
        $this->executeCommand('ParentDirectory/Product', [
            ' ', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            ' ', // Features
            ' ', // Auto Register?
        ]);

        // Assert the default content post type is always there
        $this->assertStringContainsString('Test Post Type File Not Being Overwritten', $this->getMockFileContents($postTypePath));
    }

    public function test_the_user_sees_an_error_message_if_he_tries_to_create_post_type_that_already_exists_in_sub_folder()
    {
        // Create Product file
        $postTypePath = 'app/PostTypes/ParentDirectory/Product.php';
        mkdir($this->getMockPath('app/PostTypes/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($postTypePath), ['Test Post Type File Not Being Overwritten']);

        // Try to create a second Product
        $output = $this->executeCommand('ParentDirectory/Product', [
            ' ', // Plural
            ' ', // WordPress Post Name
            ' ', // Slug
            ' ', // Features
            ' ', // Auto Register?
        ]);

        // Assert we see an error message
        $this->assertStringContainsString('Post Type Product already exists.', $output);
    }

    protected function executeCommand($name, array $input)
    {
        $this->mockWordPressLanguageFunctions();
        $app = $this->appWithMockBasePath();
        mkdir($this->getMockPath('config'));
        file_put_contents(
            $this->getMockPath('config/posttypes.php'),
            "<?php
return [
    /**
     * List all the sub-classes of Rareloop\Lumberjack\Post in your app that you wish to
     * automatically register with WordPress as part of the bootstrap process.
     */
    'register' => [
    ],
];",
        );

        $command = new PostTypeMakeCommand($app);
        $commandTester = new CommandTester($command);

        $commandTester->setInputs($input);

        $commandTester->execute(['name' => $name]);

        return $commandTester->getDisplay();
    }

    protected function getPostTypeConfig(ReflectionClass $class)
    {
        $method = $class->getMethod('getPostTypeConfig');
        $method->setAccessible(true);

        return $method->invoke(null);
    }

    protected function mockWordPressLanguageFunctions(string $namespace = 'App\PostTypes')
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
