<?php

namespace themes\Wordpress\Framework\Core\Test\Commands;

use themes\Wordpress\Framework\Core\Artisan\Commands\ControllerMakeCommand;
use themes\Wordpress\Framework\Core\Test\CommandMakerTestCase;
use ReflectionClass;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @preserveGlobalState disabled
 * @runTestsInSeparateProcesses
 */
class ControllerMakeTest extends CommandMakerTestCase
{
    public function test_it_can_create_controller()
    {
        $output = $this->executeCommand('MyController');

        // Assert the file was created
        $relativePath = 'app/Http/Controllers/MyController.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Http\Controllers\MyController::class);
        $this->assertTrue($class->isSubclassOf(\App\Http\Controllers\Controller::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php
                
                namespace App\Http\Controllers;

                
                class MyController extends Controller
                {
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Controller was successfully composed.
        $this->assertStringContainsString('MyController Controller successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Http/Controllers/MyController.php', $output);
    }

    public function test_it_not_create_controller_if_controller_already_exists()
    {
        // Create MyController file
        $controllerPath = 'app/Http/Controllers/MyController.php';
        mkdir($this->getMockPath('app/Controllers'), 0777, true);
        file_put_contents($this->getMockPath($controllerPath), ['Test Controller File Not Being Overwritten']);

        // Try to create a second MyController
        $output = $this->executeCommand('MyController');

        // Assert the default content controller is always there
        $this->assertStringContainsString('Test Controller File Not Being Overwritten', $this->getMockFileContents($controllerPath));
        // Assert we see an error message
        $this->assertStringContainsString('Controller MyController already exists.', $output);
    }

    public function test_it_can_create_controller_in_sub_folder()
    {
        $output = $this->executeCommand('ParentDirectory/MyController');

        // Assert the file was created
        $relativePath = 'app/Http/Controllers/ParentDirectory/MyController.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\Http\Controllers\ParentDirectory\MyController::class);
        $this->assertTrue($class->isSubclassOf(\App\Http\Controllers\Controller::class));

        // Content
        $this->assertSame(
            <<<CLASS
                <?php
                
                namespace App\Http\Controllers\ParentDirectory;
                
                use App\Http\Controllers\Controller;
                
                class MyController extends Controller
                {
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Controller was successfully composed.
        $this->assertStringContainsString('MyController Controller successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('app/Http/Controllers/ParentDirectory/MyController.php', $output);
    }

    public function test_it_does_not_create_controller_if_one_already_exists_in_sub_folder()
    {
        // Create ParentDirectory/MyController file
        $controllerPath = 'app/Http/Controllers/ParentDirectory/MyController.php';
        mkdir($this->getMockPath('app/Http/Controllers/ParentDirectory'), 0777, true);
        file_put_contents($this->getMockPath($controllerPath), ['Test Controller File Not Being Overwritten']);

        // Try to create a second ParentDirectory/MyController
        $output = $this->executeCommand('ParentDirectory/MyController');

        // Assert the default content controller is always there
        $this->assertStringContainsString('Test Controller File Not Being Overwritten', $this->getMockFileContents($controllerPath));
        // Assert we see an error message
        $this->assertStringContainsString('Controller MyController already exists.', $output);
    }

    public function test_it_create_template_controller()
    {
        $output = $this->executeCommand('InternalPage', true);

        // Assert the file was created
        $relativePath = 'page-templates/InternalPage.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\InternalPageController::class);
        $this->assertTrue($class->isSubclassOf(\App\Http\Controllers\Controller::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                /**
                * Template Name: Internal Page
                */
                
                namespace App;
                
                use App\Http\Controllers\Controller;
                use Rareloop\Lumberjack\Http\Responses\TimberResponse;
                use Timber\Timber;
                use Timber\Post;
                
                class InternalPageController extends Controller
                {
                  /**
                  * Handle the request.
                  *
                  * @return TimberResponse
                  * @throws \Rareloop\Lumberjack\Exceptions\TwigTemplateNotFoundException
                  */
                  public function handle(): TimberResponse
                  {
                    $context = Timber::get_context();
                
                    $context['post'] = new Post();
                
                    $context['controller_path'] = get_page_template_slug();
                    $context['controller_name'] = pathinfo(__FILE__, PATHINFO_FILENAME);
                
                    return new TimberResponse('templates/internal-page.twig', $context);
                  }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Controller was successfully composed.
        $this->assertStringContainsString('InternalPage Controller successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('page-templates/InternalPage.php', $output);
        // Assert we have the view in the output
        $this->assertStringContainsString('resources/views/templates/internal-page.twig', $output);
    }

    public function test_it_create_named_template_controller()
    {
        $output = $this->executeCommand('InternalPage', 'Page Interne');

        // Assert the file was created
        $relativePath = 'page-templates/InternalPage.php';
        $this->assertMockPath($relativePath);
        $this->requireMockFile($relativePath);

        // Assert we can instantiate it and make inferences on it's properties
        $class = new ReflectionClass(\App\InternalPageController::class);
        $this->assertTrue($class->isSubclassOf(\App\Http\Controllers\Controller::class));

        // Content
        $this->assertSame(
            <<<'CLASS'
                <?php
                
                /**
                * Template Name: Page Interne
                */
                
                namespace App;
                
                use App\Http\Controllers\Controller;
                use Rareloop\Lumberjack\Http\Responses\TimberResponse;
                use Timber\Timber;
                use Timber\Post;
                
                class InternalPageController extends Controller
                {
                  /**
                  * Handle the request.
                  *
                  * @return TimberResponse
                  * @throws \Rareloop\Lumberjack\Exceptions\TwigTemplateNotFoundException
                  */
                  public function handle(): TimberResponse
                  {
                    $context = Timber::get_context();
                
                    $context['post'] = new Post();
                
                    $context['controller_path'] = get_page_template_slug();
                    $context['controller_name'] = pathinfo(__FILE__, PATHINFO_FILENAME);
                
                    return new TimberResponse('templates/internal-page.twig', $context);
                  }
                }

                CLASS
            ,
            $this->getMockFileContents($relativePath),
        );

        // Assert we have the message that tell the user the Controller was successfully composed.
        $this->assertStringContainsString('InternalPage Controller successfully composed.', $output);
        // Assert we have the path in the output.
        $this->assertStringContainsString('page-templates/InternalPage.php', $output);
        // Assert we have the view in the output
        $this->assertStringContainsString('resources/views/templates/internal-page.twig', $output);
    }

    protected function setUp(): void
    {
        parent::setUp();
        mkdir($this->getMockPath('app'));
        mkdir($this->getMockPath('app/Http'));
        mkdir($this->getMockPath('app/Http/Controllers'));
        file_put_contents(
            $this->getMockPath('app/Http/Controllers/Controller.php'),
            <<<CLASS
                <?php
                
                namespace App\Http\Controllers;
                
                use themes\Wordpress\Framework\Core\Controller as BaseController;
                
                class Controller extends BaseController
                {
                }
                
                CLASS
        );
        $this->requireMockFile('app/Http/Controllers/Controller.php');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        rmdir($this->getmockPath('app'));
    }

    /**
     * Execute the command.
     *
     * @param string      $name
     * @param bool|string $template
     *
     * @return string
     */
    protected function executeCommand(string $name, $template = false): string
    {
        $app = $this->appWithMockBasePath();

        $command = new ControllerMakeCommand($app);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'name'       => $name,
            '--template' => $template,
        ]);

        return $commandTester->getDisplay();
    }
}
