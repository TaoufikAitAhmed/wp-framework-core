<?php

namespace themes\Wordpress\Framework\Core\Artisan\Commands;

use themes\Wordpress\Framework\Core\Artisan\OutputStyle;
use themes\Wordpress\Framework\Core\Artisan\Parser;
use Exception;
use Illuminate\Console\Concerns\InteractsWithIO;
use Rareloop\Lumberjack\Application;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends SymfonyCommand
{
    use InteractsWithIO;

    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * The console command name.
     *
     * @var string
     */
    protected string $name = '';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected string $signature = '';

    /**
     * The console command description.
     *
     * @var string
     */
    protected string $description = '';

    /**
     * The console command help text.
     *
     * @var string
     */
    protected $help;

    public function __construct(Application $app)
    {
        $this->app = $app;

        if (isset($this->signature)) {
            $this->configureUsingFluentDefinition();
        } else {
            parent::__construct($this->name);
        }

        // Once we have constructed the command, we'll set the description and other
        // related properties of the command. If a signature wasn't used to build
        // the command we'll set the arguments and the options on this command.
        if (isset($this->description)) {
            $this->setDescription($this->description);
        }

        if (isset($this->help)) {
            $this->setHelp($this->help);
        }
    }

    /**
     * Run the console command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $this->app->make(OutputStyle::class, [
            'input'  => $input,
            'output' => $output,
        ]);

        return parent::run($this->input = $input, $this->output);
    }

    /**
     * Call another console command.
     *
     * @param string $command
     * @param array  $arguments
     *
     * @return int
     * @throws Exception
     */
    public function call(string $command, array $arguments = []): int
    {
        $arguments['command'] = $command;

        return $this->getApplication()->find($command)->run(
            $this->createInputFromArguments($arguments),
            $this->output
        );
    }

    /**
     * Create an input instance from the given arguments.
     *
     * @param array $arguments
     *
     * @return ArrayInput
     */
    protected function createInputFromArguments(array $arguments): ArrayInput
    {
        return tap(new ArrayInput(array_merge($this->context(), $arguments)), function ($input) {
            if ($input->hasParameterOption(['--no-interaction'], true)) {
                $input->setInteractive(false);
            }
        });
    }

    /**
     * Get all of the context passed to the command.
     *
     * @return array
     */
    protected function context(): array
    {
        return collect($this->option())->only([
            'ansi',
            'no-ansi',
            'no-interaction',
            'quiet',
            'verbose',
        ])->filter()->mapWithKeys(function ($value, $key) {
            return ["--{$key}" => $value];
        })->all();
    }

    /**
     * Configure the console command using a fluent definition.
     *
     * @return void
     */
    protected function configureUsingFluentDefinition()
    {
        [$name, $arguments, $options] = Parser::parse($this->signature);

        parent::__construct($this->name = $name);

        // After parsing the signature we will spin through the arguments and options
        // and set them on this command. These will already be changed into proper
        // instances of these "InputArgument" and "InputOption" Symfony classes.
        $this->getDefinition()->addArguments($arguments);
        $this->getDefinition()->addOptions($options);
    }
}
