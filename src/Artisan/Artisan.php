<?php

namespace themes\Wordpress\Framework\Core\Artisan;

use themes\Wordpress\Framework\Core\Artisan\Commands\Acf\FieldMakeCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\Acf\OptionsMakeCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\Acf\PageMakeCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\Acf\PartialMakeCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\ControllerMakeCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\Database\FreshCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\ExceptionMakeCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\FactoryMakeCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\PackageDiscoverCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\PagesListCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\PluginMakeCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\PostTypeMakeCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\RouteListCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\SeedCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\SeederMakeCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\ServiceProviderMakeCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\TaxonomyMakeCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\VendorPublishCommand;
use themes\Wordpress\Framework\Core\Artisan\Commands\ViewModelMakeCommand;
use themes\Wordpress\Framework\Core\Bootstrappers\LoadConfiguration;
use themes\Wordpress\Framework\Core\Bootstrappers\RegisterProviders;
use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Bootstrappers\BootProviders;
use Rareloop\Lumberjack\Bootstrappers\RegisterExceptionHandler;
use Rareloop\Lumberjack\Bootstrappers\RegisterFacades;
use Symfony\Component\Console\Application as ConsoleApplication;

class Artisan
{
    protected array $bootstrappers = [
        RegisterExceptionHandler::class,
        LoadConfiguration::class,
        RegisterFacades::class,
        RegisterProviders::class,
        BootProviders::class,
        RegisterCommands::class,
    ];

    protected array $defaultCommands = [
        PagesListCommand::class,
        ControllerMakeCommand::class,
        ExceptionMakeCommand::class,
        PluginMakeCommand::class,
        ServiceProviderMakeCommand::class,
        ViewModelMakeCommand::class,
        RouteListCommand::class,
        PostTypeMakeCommand::class,
        TaxonomyMakeCommand::class,
        FieldMakeCommand::class,
        PageMakeCommand::class,
        PartialMakeCommand::class,
        OptionsMakeCommand::class,
        PackageDiscoverCommand::class,
        VendorPublishCommand::class,
        SeederMakeCommand::class,
        FactoryMakeCommand::class,
        SeedCommand::class,
        FreshCommand::class,
    ];

    private Application $app;

    /**
     * @var mixed
     */
    private $consoleApp;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->consoleApp = $this->app->make(ConsoleApplication::class, ['name' => 'Artisan - themes Lumberjack CLI']);

        $this->app->bind(self::class, $this);
    }

    public function bootstrap()
    {
        $this->loadDefaultCommands();
        $this->app->bootstrapWith($this->bootstrappers());
    }

    public function run()
    {
        $this->consoleApp->run();
    }

    public function console()
    {
        return $this->consoleApp;
    }

    public function defaultCommands(): array
    {
        return $this->defaultCommands;
    }

    protected function loadDefaultCommands()
    {
        foreach ($this->defaultCommands() as $command) {
            $this->consoleApp->add($this->app->make($command));
        }
    }

    protected function bootstrappers(): array
    {
        return $this->bootstrappers;
    }
}
