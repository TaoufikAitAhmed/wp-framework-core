<?php
/**
 * PHPUnit bootstrap file
 */

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available

use themes\Wordpress\Framework\Core\Application;
use themes\Wordpress\Framework\Core\Artisan\Artisan;
use Rareloop\Lumberjack\Http\Lumberjack as LumberjackApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

function rmdir_recursive($dir)
{
    foreach (scandir($dir) as $file) {
        if ('.' === $file || '..' === $file) {
            continue;
        }
        if (is_dir("$dir/$file")) {
            rmdir_recursive("$dir/$file");
        } else {
            unlink("$dir/$file");
        }
    }
    rmdir($dir);
}

function recursive_copy($source, $destination)
{
    if (!file_exists($destination)) {
        mkdir($destination);
    }

    $splFileInfoArr = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

    foreach ($splFileInfoArr as $fullPath => $splFileinfo) {
        //skip . ..
        if (in_array($splFileinfo->getBasename(), ['.', '..'])) {
            continue;
        }
        //get relative path of source file or folder
        $path = str_replace($source, '', $splFileinfo->getPathname());

        if ($splFileinfo->isDir()) {
            mkdir($destination . '/' . $path);
        } else {
            copy($fullPath, $destination . '/' . $path);
        }
    }
}

function call_artisan_command($name, array $params = []): BufferedOutput
{
    global $app;
    /** @var Artisan $artisan */
    $artisan = $app->get(Artisan::class);
    $artisan->console()->setAutoExit(false);

    $input = new ArrayInput(
        [
            'command' => $name,
        ] + $params,
    );

    $output = new BufferedOutput();
    $artisan->console()->run($input, $output);

    return $output;
}

function reset_theme()
{
    // Reset theme
    rmdir_recursive(dirname(__DIR__) . '/theme');
    mkdir(dirname(__DIR__) . '/theme');
    mkdir(dirname(__DIR__) . '/theme/bootstrap');
    mkdir(dirname(__DIR__) . '/theme/bootstrap/cache');
}

reset_theme();

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// Give access to tests_add_filter() function.
require_once getenv('WP_PHPUNIT__DIR') . '/includes/functions.php';

tests_add_filter('muplugins_loaded', function () {
    // test set up, plugin activation, etc.
    if (!function_exists('wp_get_current_user')) {
        include ABSPATH . 'wp-includes/pluggable.php';
    }

    // Bootstrap the core
    global $app;
    $app = new Application(dirname(__DIR__) . '/theme');
    $app->bind(LumberjackApplication::class, $app);
    global $lumberjack;
    $lumberjack = $app->make(\themes\Wordpress\Framework\Core\Http\Lumberjack::class);
    $artisan = $app->get(Artisan::class);
    $artisan->bootstrap();
    $lumberjack->bootstrap();
    $app->bind(Rareloop\Lumberjack\Exceptions\HandlerInterface::class, $app->make(\themes\Wordpress\Framework\Core\Artisan\Exceptions\Handler::class));

    // Load ACF Pro
    require dirname(__FILE__) . '/../wordpress/wp-content/plugins/advanced-custom-fields-pro/acf.php';
});

// Start up the WP testing environment.
require getenv('WP_PHPUNIT__DIR') . '/includes/bootstrap.php';
